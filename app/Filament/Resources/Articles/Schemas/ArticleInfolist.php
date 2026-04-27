<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Models\Article;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Throwable;

class ArticleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Artikel')
                    ->description('Data utama artikel.')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('title')
                            ->label('Judul')
                            ->columnSpan(2),

                        TextEntry::make('slug')
                            ->label('Slug')
                            ->placeholder('-'),

                        IconEntry::make('is_published')
                            ->label('Dipublikasikan')
                            ->boolean(),

                        TextEntry::make('published_at')
                            ->label('Tanggal Publikasi')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),

                Section::make('SEO')
                    ->description('Data SEO artikel untuk tampilan mesin pencari.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('seo_title')
                            ->label('SEO Title')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('seo_description')
                            ->label('SEO Description')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Informasi Sistem')
                    ->description('Metadata pembuatan, pembaruan, dan soft delete.')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('deleted_at')
                            ->label('Dihapus Pada')
                            ->dateTime()
                            ->placeholder('-')
                            ->visible(fn (Article $record): bool => $record->trashed()),
                    ]),

                Section::make('Konten Artikel')
                    ->description('Daftar konten berdasarkan relasi article_contents.')
                    ->schema([
                        RepeatableEntry::make('contents')
                            ->label('Bagian Konten')
                            ->contained(false)
                            ->placeholder('Belum ada konten.')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('tags')
                                    ->label('Tags')
                                    ->badge()
                                    ->listWithLineBreaks()
                                    ->placeholder('-'),

                                TextEntry::make('content')
                                    ->label('Isi Konten')
                                    ->formatStateUsing(fn (mixed $state): HtmlString => new HtmlString(self::renderBuilderContent($state)))
                                    ->prose()
                                    ->placeholder('-')
                                    ->columnSpanFull(),

                                TextEntry::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->dateTime()
                                    ->placeholder('-'),

                                TextEntry::make('updated_at')
                                    ->label('Diperbarui Pada')
                                    ->dateTime()
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(3);
    }

    protected static function renderBuilderContent(mixed $content): string
    {
        if (blank($content)) {
            return '-';
        }

        $builderBlocks = self::decodeBuilderBlocks($content);

        if (! is_array($builderBlocks)) {
            if (is_string($content)) {
                return Str::sanitizeHtml($content);
            }

            return '-';
        }

        $html = collect($builderBlocks)
            ->map(function (mixed $builderBlock): ?string {
                if (! is_array($builderBlock)) {
                    return null;
                }

                $blockType = (string) ($builderBlock['type'] ?? '');
                $builderBlockHtml = self::renderBuilderBlock($builderBlock);

                if (blank($builderBlockHtml)) {
                    return null;
                }

                return '<article><h4>'.e(self::resolveBlockLabel($blockType)).'</h4>'.$builderBlockHtml.'</article>';
            })
            ->filter(fn (?string $builderBlockHtml): bool => filled($builderBlockHtml))
            ->implode(PHP_EOL);

        return filled($html) ? Str::sanitizeHtml($html) : '-';
    }

    protected static function decodeBuilderBlocks(mixed $content): ?array
    {
        if (is_string($content)) {
            $decodedContent = json_decode($content, true);
        } elseif (is_array($content)) {
            $decodedContent = $content;
        } else {
            return null;
        }

        if (! is_array($decodedContent)) {
            return null;
        }

        if (is_array($decodedContent['blocks'] ?? null)) {
            $decodedContent = $decodedContent['blocks'];
        }

        if (
            ! array_is_list($decodedContent)
            && array_key_exists('type', $decodedContent)
            && array_key_exists('data', $decodedContent)
        ) {
            $decodedContent = [$decodedContent];
        }

        if (! array_is_list($decodedContent)) {
            if (
                array_key_exists('type', $decodedContent)
                || array_key_exists('content', $decodedContent)
            ) {
                return null;
            }

            $decodedContent = array_values($decodedContent);
        }

        if (! is_array($decodedContent)) {
            return null;
        }

        if (! isset($decodedContent[0])) {
            return [];
        }

        if (! is_array($decodedContent[0])) {
            return null;
        }

        if (! array_key_exists('type', $decodedContent[0]) || ! array_key_exists('data', $decodedContent[0])) {
            return null;
        }

        return $decodedContent;
    }

    protected static function resolveBlockLabel(string $blockType): string
    {
        return match ($blockType) {
            'heading' => 'Heading',
            'rich_text' => 'Rich Text',
            'image' => 'Image',
            'quote' => 'Quote',
            'divider' => 'Divider',
            'hero' => 'Hero',
            'section_rich' => 'Section Rich Text',
            'features' => 'Features Grid',
            'cta' => 'CTA',
            'faq' => 'FAQ',
            'testimonials' => 'Testimonials',
            'spacer' => 'Spacer',
            'custom_html' => 'Custom HTML',
            default => Str::headline($blockType),
        };
    }

    protected static function renderBuilderBlock(mixed $builderBlock): ?string
    {
        if (! is_array($builderBlock)) {
            return null;
        }

        $blockType = (string) ($builderBlock['type'] ?? '');
        $blockData = is_array($builderBlock['data'] ?? null) ? $builderBlock['data'] : [];

        if (blank($blockType)) {
            return null;
        }

        return match ($blockType) {
            'heading' => self::renderHeadingBlock($blockData),
            'rich_text' => self::renderRichTextBlock($blockData),
            'image' => self::renderImageBlock($blockData),
            'quote' => self::renderQuoteBlock($blockData),
            'divider' => '<hr>',
            'hero' => self::renderHeroBlock($blockData),
            'section_rich' => self::renderSectionRichBlock($blockData),
            'features' => self::renderFeaturesBlock($blockData),
            'cta' => self::renderCtaBlock($blockData),
            'faq' => self::renderFaqBlock($blockData),
            'testimonials' => self::renderTestimonialsBlock($blockData),
            'spacer' => self::renderSpacerBlock($blockData),
            'custom_html' => self::renderCustomHtmlBlock($blockData),
            default => self::renderUnknownBlock($blockType, $blockData),
        };
    }

    /**
     * @param  array<string, mixed>  $blockData
     */
    protected static function renderHeadingBlock(array $blockData): ?string
    {
        $headingText = trim((string) ($blockData['content'] ?? ''));

        if (blank($headingText)) {
            return null;
        }

        $headingLevel = strtolower((string) ($blockData['level'] ?? 'h2'));

        if (! in_array($headingLevel, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true)) {
            $headingLevel = 'h2';
        }

        return "<{$headingLevel}>".e($headingText)."</{$headingLevel}>";
    }

    /**
     * @param  array<string, mixed>  $blockData
     */
    protected static function renderRichTextBlock(array $blockData): ?string
    {
        $richTextHtml = self::normalizeRichTextContentState($blockData['content'] ?? null);

        if (is_array($richTextHtml)) {
            try {
                return RichContentRenderer::make($richTextHtml)->toHtml();
            } catch (Throwable) {
                return null;
            }
        }

        return is_string($richTextHtml) && filled($richTextHtml) ? $richTextHtml : null;
    }

    /**
     * @return string|array<string, mixed>|null
     */
    protected static function normalizeRichTextContentState(mixed $value): string|array|null
    {
        if (is_array($value)) {
            return self::normalizeTipTapDocument($value);
        }

        if (! is_string($value)) {
            return null;
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return self::normalizeTipTapDocument($decoded);
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>|array<int, mixed>  $document
     * @return array<string, mixed>
     */
    protected static function normalizeTipTapDocument(array $document): array
    {
        if (array_is_list($document)) {
            return [
                'type' => 'doc',
                'content' => self::normalizeTipTapNodeList($document),
            ];
        }

        if (($document['type'] ?? null) === 'doc') {
            return [
                'type' => 'doc',
                'content' => is_array($document['content'] ?? null)
                    ? self::normalizeTipTapNodeList($document['content'])
                    : [],
            ];
        }

        if (is_string($document['type'] ?? null)) {
            $normalizedNode = self::normalizeTipTapNode($document);

            return [
                'type' => 'doc',
                'content' => $normalizedNode ? [$normalizedNode] : [],
            ];
        }

        if (is_array($document['content'] ?? null)) {
            return [
                'type' => 'doc',
                'content' => self::normalizeTipTapNodeList($document['content']),
            ];
        }

        return [
            'type' => 'doc',
            'content' => [],
        ];
    }

    /**
     * @param  array<int, mixed>  $nodes
     * @return array<int, array<string, mixed>>
     */
    protected static function normalizeTipTapNodeList(array $nodes): array
    {
        $normalizedNodes = [];

        foreach ($nodes as $node) {
            $normalizedNode = self::normalizeTipTapNode($node);

            if ($normalizedNode !== null) {
                $normalizedNodes[] = $normalizedNode;
            }
        }

        return $normalizedNodes;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function normalizeTipTapNode(mixed $node): ?array
    {
        if (! is_array($node)) {
            return null;
        }

        $type = trim((string) ($node['type'] ?? ''));

        if ($type === '') {
            return null;
        }

        if (isset($node['content'])) {
            if (is_array($node['content'])) {
                $node['content'] = self::normalizeTipTapNodeList($node['content']);
            } else {
                unset($node['content']);
            }
        }

        if ($type === 'heading') {
            $attrs = is_array($node['attrs'] ?? null) ? $node['attrs'] : [];
            $level = (int) ($attrs['level'] ?? 2);
            $attrs['level'] = max(1, min(6, $level));
            $node['attrs'] = $attrs;
        }

        $node['type'] = $type;

        return $node;
    }

    /**
     * @param  array<string, mixed>  $blockData
     */
    protected static function renderImageBlock(array $blockData): ?string
    {
        $imagePath = self::normalizeSingleFileValue($blockData['url'] ?? null);

        if (blank($imagePath)) {
            return null;
        }

        $imageUrl = self::resolveImageUrl($imagePath);

        if (blank($imageUrl)) {
            return null;
        }

        return '<figure><img src="'.e($imageUrl).'" alt="'.e((string) ($blockData['alt'] ?? '')).'" class="max-w-full rounded"></figure>';
    }

    /**
     * @param  array<string, mixed>  $blockData
     */
    protected static function renderQuoteBlock(array $blockData): ?string
    {
        $quoteText = trim((string) ($blockData['quote'] ?? ''));

        if (blank($quoteText)) {
            return null;
        }

        $quoteHtml = '<blockquote><p>'.e($quoteText).'</p>';
        $quoteCite = trim((string) ($blockData['cite'] ?? ''));

        if (filled($quoteCite)) {
            $quoteHtml .= '<cite>'.e($quoteCite).'</cite>';
        }

        $quoteHtml .= '</blockquote>';

        return $quoteHtml;
    }

    /**
     * @param  array<string, mixed>  $blockData
     */
    protected static function renderHeroBlock(array $blockData): string
    {
        $headline = trim((string) ($blockData['headline'] ?? ''));
        $subheadline = trim((string) ($blockData['subheadline'] ?? ''));
        $primaryCtaLabel = trim((string) ($blockData['primary_cta_label'] ?? ''));
        $primaryCtaUrl = trim((string) ($blockData['primary_cta_url'] ?? ''));
        $secondaryCtaLabel = trim((string) ($blockData['secondary_cta_label'] ?? ''));
        $secondaryCtaUrl = trim((string) ($blockData['secondary_cta_url'] ?? ''));
        $imagePath = self::normalizeSingleFileValue($blockData['image'] ?? null);

        $html = '';

        if (filled($headline)) {
            $html .= '<h5>'.e($headline).'</h5>';
        }

        if (filled($subheadline)) {
            $html .= '<p>'.e($subheadline).'</p>';
        }

        $ctaItems = [];

        if (filled($primaryCtaLabel) && filled($primaryCtaUrl)) {
            $ctaItems[] = '<li><a href="'.e($primaryCtaUrl).'">'.e($primaryCtaLabel).'</a></li>';
        }

        if (filled($secondaryCtaLabel) && filled($secondaryCtaUrl)) {
            $ctaItems[] = '<li><a href="'.e($secondaryCtaUrl).'">'.e($secondaryCtaLabel).'</a></li>';
        }

        if ($ctaItems !== []) {
            $html .= '<ul>'.implode('', $ctaItems).'</ul>';
        }

        if (filled($imagePath)) {
            $imageUrl = self::resolveImageUrl($imagePath);

            if (filled($imageUrl)) {
                $html .= '<figure><img src="'.e($imageUrl).'" alt="'.e($headline ?: 'Hero Image').'"></figure>';
            }
        }

        return $html;
    }

    /**
     * @param  array<string, mixed>  $blockData
     */
    protected static function renderSectionRichBlock(array $blockData): string
    {
        $title = trim((string) ($blockData['title'] ?? ''));
        $content = is_string($blockData['content'] ?? null) ? (string) $blockData['content'] : '';
        $withDivider = (bool) ($blockData['with_divider'] ?? false);

        $html = '';

        if (filled($title)) {
            $html .= '<h5>'.e($title).'</h5>';
        }

        if (filled($content)) {
            $html .= $content;
        }

        if ($withDivider) {
            $html .= '<hr>';
        }

        return $html;
    }

    /**
     * @param  array<string, mixed>  $blockData
     */
    protected static function renderFeaturesBlock(array $blockData): string
    {
        $title = trim((string) ($blockData['title'] ?? ''));
        $subtitle = trim((string) ($blockData['subtitle'] ?? ''));
        $items = is_array($blockData['items'] ?? null) ? $blockData['items'] : [];

        $html = '';

        if (filled($title)) {
            $html .= '<h5>'.e($title).'</h5>';
        }

        if (filled($subtitle)) {
            $html .= '<p>'.e($subtitle).'</p>';
        }

        $featureItems = collect($items)
            ->map(function (mixed $featureItem): ?string {
                if (! is_array($featureItem)) {
                    return null;
                }

                $featureTitle = trim((string) ($featureItem['title'] ?? ''));
                $featureDescription = trim((string) ($featureItem['description'] ?? ''));

                if (blank($featureTitle) && blank($featureDescription)) {
                    return null;
                }

                $featureHtml = '';

                if (filled($featureTitle)) {
                    $featureHtml .= '<strong>'.e($featureTitle).'</strong>';
                }

                if (filled($featureDescription)) {
                    $featureHtml .= '<p>'.e($featureDescription).'</p>';
                }

                return '<li>'.$featureHtml.'</li>';
            })
            ->filter(fn (?string $featureHtml): bool => filled($featureHtml))
            ->implode('');

        if (filled($featureItems)) {
            $html .= '<ul>'.$featureItems.'</ul>';
        }

        return $html;
    }

    /**
     * @param  array<string, mixed>  $blockData
     */
    protected static function renderCtaBlock(array $blockData): string
    {
        $title = trim((string) ($blockData['title'] ?? ''));
        $description = trim((string) ($blockData['description'] ?? ''));
        $buttonLabel = trim((string) ($blockData['button_label'] ?? ''));
        $buttonUrl = trim((string) ($blockData['button_url'] ?? ''));

        $html = '';

        if (filled($title)) {
            $html .= '<h5>'.e($title).'</h5>';
        }

        if (filled($description)) {
            $html .= '<p>'.e($description).'</p>';
        }

        if (filled($buttonLabel) && filled($buttonUrl)) {
            $html .= '<p><a href="'.e($buttonUrl).'">'.e($buttonLabel).'</a></p>';
        }

        return $html;
    }

    /**
     * @param  array<string, mixed>  $blockData
     */
    protected static function renderFaqBlock(array $blockData): string
    {
        $title = trim((string) ($blockData['title'] ?? ''));
        $items = is_array($blockData['items'] ?? null) ? $blockData['items'] : [];

        $html = '';

        if (filled($title)) {
            $html .= '<h5>'.e($title).'</h5>';
        }

        $faqItems = collect($items)
            ->map(function (mixed $faqItem): ?string {
                if (! is_array($faqItem)) {
                    return null;
                }

                $question = trim((string) ($faqItem['q'] ?? ''));
                $answer = trim((string) ($faqItem['a'] ?? ''));

                if (blank($question) && blank($answer)) {
                    return null;
                }

                $faqHtml = '';

                if (filled($question)) {
                    $faqHtml .= '<strong>'.e($question).'</strong>';
                }

                if (filled($answer)) {
                    $faqHtml .= '<p>'.e($answer).'</p>';
                }

                return '<li>'.$faqHtml.'</li>';
            })
            ->filter(fn (?string $faqHtml): bool => filled($faqHtml))
            ->implode('');

        if (filled($faqItems)) {
            $html .= '<ol>'.$faqItems.'</ol>';
        }

        return $html;
    }

    /**
     * @param  array<string, mixed>  $blockData
     */
    protected static function renderTestimonialsBlock(array $blockData): string
    {
        $title = trim((string) ($blockData['title'] ?? ''));
        $items = is_array($blockData['items'] ?? null) ? $blockData['items'] : [];

        $html = '';

        if (filled($title)) {
            $html .= '<h5>'.e($title).'</h5>';
        }

        $testimonialItems = collect($items)
            ->map(function (mixed $testimonialItem): ?string {
                if (! is_array($testimonialItem)) {
                    return null;
                }

                $name = trim((string) ($testimonialItem['name'] ?? ''));
                $role = trim((string) ($testimonialItem['role'] ?? ''));
                $quote = trim((string) ($testimonialItem['quote'] ?? ''));
                $avatarPath = self::normalizeSingleFileValue($testimonialItem['avatar'] ?? null);

                if (blank($name) && blank($role) && blank($quote)) {
                    return null;
                }

                $testimonialHtml = '';

                if (filled($avatarPath)) {
                    $avatarUrl = self::resolveImageUrl($avatarPath);

                    if (filled($avatarUrl)) {
                        $testimonialHtml .= '<img src="'.e($avatarUrl).'" alt="'.e($name ?: 'Avatar').'">';
                    }
                }

                if (filled($quote)) {
                    $testimonialHtml .= '<blockquote><p>'.e($quote).'</p></blockquote>';
                }

                if (filled($name)) {
                    $testimonialHtml .= '<p><strong>'.e($name).'</strong></p>';
                }

                if (filled($role)) {
                    $testimonialHtml .= '<p>'.e($role).'</p>';
                }

                return '<li>'.$testimonialHtml.'</li>';
            })
            ->filter(fn (?string $testimonialHtml): bool => filled($testimonialHtml))
            ->implode('');

        if (filled($testimonialItems)) {
            $html .= '<ul>'.$testimonialItems.'</ul>';
        }

        return $html;
    }

    /**
     * @param  array<string, mixed>  $blockData
     */
    protected static function renderSpacerBlock(array $blockData): string
    {
        $size = (string) ($blockData['size'] ?? 'md');
        $label = match ($size) {
            'sm' => 'Spacer Small',
            'lg' => 'Spacer Large',
            'xl' => 'Spacer Extra Large',
            default => 'Spacer Medium',
        };

        return '<p><em>'.e($label).'</em></p><hr>';
    }

    /**
     * @param  array<string, mixed>  $blockData
     */
    protected static function renderCustomHtmlBlock(array $blockData): string
    {
        $html = is_string($blockData['html'] ?? null) ? (string) $blockData['html'] : '';
        $meta = is_array($blockData['meta'] ?? null) ? $blockData['meta'] : [];

        $metaHtml = collect($meta)
            ->map(function (mixed $metaValue, mixed $metaKey): ?string {
                if (! is_scalar($metaValue) && $metaValue !== null) {
                    return null;
                }

                $metaKeyString = trim((string) $metaKey);
                $metaValueString = trim((string) ($metaValue ?? ''));

                if (blank($metaKeyString) || blank($metaValueString)) {
                    return null;
                }

                return '<li><strong>'.e($metaKeyString).':</strong> '.e($metaValueString).'</li>';
            })
            ->filter(fn (?string $metaRow): bool => filled($metaRow))
            ->implode('');

        if (filled($metaHtml)) {
            $html .= '<ul>'.$metaHtml.'</ul>';
        }

        return $html;
    }

    /**
     * @param  array<string, mixed>  $blockData
     */
    protected static function renderUnknownBlock(string $blockType, array $blockData): ?string
    {
        if ($blockData === []) {
            return '<p><em>Block '.e(Str::headline($blockType)).'</em></p>';
        }

        $json = json_encode($blockData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        if (! is_string($json) || blank($json)) {
            return null;
        }

        return '<p><strong>'.e(Str::headline($blockType)).'</strong></p><pre><code>'.e($json).'</code></pre>';
    }

    protected static function normalizeSingleFileValue(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if (! is_array($value)) {
            return null;
        }

        if (array_is_list($value)) {
            foreach ($value as $item) {
                $normalizedItem = self::normalizeSingleFileValue($item);

                if (filled($normalizedItem)) {
                    return $normalizedItem;
                }
            }

            return null;
        }

        foreach (['path', 'url', 'value'] as $key) {
            if (is_string($value[$key] ?? null) && filled($value[$key])) {
                return $value[$key];
            }
        }

        return null;
    }

    protected static function resolveImageUrl(string $imagePath): ?string
    {
        if (Str::startsWith($imagePath, ['http://', 'https://', 'data:', '/'])) {
            return $imagePath;
        }

        $disk = (string) (config('filament.default_filesystem_disk') ?? config('filesystems.default'));
        $normalizedImagePath = ltrim($imagePath, '/');

        if (blank($disk)) {
            return $normalizedImagePath;
        }

        try {
            $filesystem = Storage::disk($disk);

            if (is_object($filesystem) && method_exists($filesystem, 'url')) {
                return $filesystem->url($normalizedImagePath);
            }
        } catch (Throwable) {
            //
        }

        if ($disk === 'public') {
            return Str::startsWith($normalizedImagePath, 'storage/')
                ? asset($normalizedImagePath)
                : asset('storage/'.$normalizedImagePath);
        }

        return $normalizedImagePath;
    }
}
