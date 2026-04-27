<?php

use App\Models\User;
use App\Services\ZennerAcademy\ContentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;

function makeUserForZennerTest(int $id = 1): User
{
    $user = new User;
    $user->forceFill([
        'name' => 'Admin Test',
        'email' => 'admin'.$id.'@example.test',
        'password' => bcrypt('password'),
    ]);
    $user->setAttribute('id', $id);
    $user->exists = true;

    return $user;
}

// ─── Route registration ───────────────────────────────────────────────────────

it('registers zenner academy content routes', function (): void {
    expect(route('api.zenner-academy.contents.index', [], false))->toBe('/api/zenner-academy/contents')
        ->and(route('api.zenner-academy.contents.show', ['content' => 1], false))->toBe('/api/zenner-academy/contents/1')
        ->and(route('api.zenner-academy.contents.show-by-slug', ['slug' => 'test-slug'], false))->toBe('/api/zenner-academy/contents/slug/test-slug')
        ->and(route('api.zenner-academy.contents.store', [], false))->toBe('/api/zenner-academy/contents')
        ->and(route('api.zenner-academy.contents.update', ['content' => 1], false))->toBe('/api/zenner-academy/contents/1')
        ->and(route('api.zenner-academy.contents.destroy', ['content' => 1], false))->toBe('/api/zenner-academy/contents/1')
        ->and(route('api.zenner-academy.categories.contents', ['categorySlug' => 'academy'], false))->toBe('/api/zenner-academy/categories/academy/contents');
});

// ─── index ────────────────────────────────────────────────────────────────────

it('returns paginated content list from index endpoint', function (): void {
    $this->mock(ContentService::class, function (MockInterface $mock): void {
        $paginator = new LengthAwarePaginator(
            items: [
                ['id' => 1, 'title' => 'Konten A', 'slug' => 'konten-a', 'status' => 'published'],
                ['id' => 2, 'title' => 'Konten B', 'slug' => 'konten-b', 'status' => 'draft'],
            ],
            total: 2,
            perPage: 15,
            currentPage: 1,
        );

        $mock->shouldReceive('getContentList')
            ->once()
            ->andReturn($paginator);
    });

    $this->getJson(route('api.zenner-academy.contents.index'))
        ->assertOk()
        ->assertJsonPath('message', 'Daftar konten berhasil diambil.')
        ->assertJsonPath('data.total', 2)
        ->assertJsonPath('data.data.0.title', 'Konten A');
});

it('filters content list by status query parameter', function (): void {
    $this->mock(ContentService::class, function (MockInterface $mock): void {
        $paginator = new LengthAwarePaginator(
            items: [['id' => 1, 'title' => 'Konten Published', 'status' => 'published']],
            total: 1,
            perPage: 15,
            currentPage: 1,
        );

        $mock->shouldReceive('getContentList')
            ->once()
            ->with(['search' => null, 'status' => 'published', 'category_id' => null], 15)
            ->andReturn($paginator);
    });

    $this->getJson(route('api.zenner-academy.contents.index').'?status=published')
        ->assertOk()
        ->assertJsonPath('data.data.0.status', 'published');
});

it('rejects invalid status filter with validation error', function (): void {
    $this->getJson(route('api.zenner-academy.contents.index').'?status=invalid')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

// ─── show ─────────────────────────────────────────────────────────────────────

it('returns content detail from show endpoint', function (): void {
    $this->mock(ContentService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getContentDetail')
            ->once()
            ->with(5)
            ->andReturn([
                'id' => 5,
                'title' => 'Panduan Member',
                'slug' => 'panduan-member',
                'status' => 'published',
                'category' => ['id' => 1, 'name' => 'Academy', 'slug' => 'academy'],
            ]);
    });

    $this->getJson(route('api.zenner-academy.contents.show', ['content' => 5]))
        ->assertOk()
        ->assertJsonPath('message', 'Detail konten berhasil diambil.')
        ->assertJsonPath('data.id', 5)
        ->assertJsonPath('data.title', 'Panduan Member')
        ->assertJsonPath('data.category.slug', 'academy');
});

it('returns 404 when content id does not exist', function (): void {
    $this->mock(ContentService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getContentDetail')
            ->once()
            ->with(999)
            ->andThrow(new ModelNotFoundException);
    });

    $this->getJson(route('api.zenner-academy.contents.show', ['content' => 999]))
        ->assertNotFound()
        ->assertJsonPath('message', 'Konten tidak ditemukan.');
});

it('returns content detail by slug', function (): void {
    $this->mock(ContentService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getContentDetailBySlug')
            ->once()
            ->with('panduan-member')
            ->andReturn(['id' => 5, 'title' => 'Panduan Member', 'slug' => 'panduan-member']);
    });

    $this->getJson(route('api.zenner-academy.contents.show-by-slug', ['slug' => 'panduan-member']))
        ->assertOk()
        ->assertJsonPath('data.slug', 'panduan-member');
});

it('returns 404 when content slug does not exist', function (): void {
    $this->mock(ContentService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getContentDetailBySlug')
            ->once()
            ->with('slug-tidak-ada')
            ->andThrow(new ModelNotFoundException);
    });

    $this->getJson(route('api.zenner-academy.contents.show-by-slug', ['slug' => 'slug-tidak-ada']))
        ->assertNotFound()
        ->assertJsonPath('message', 'Konten tidak ditemukan.');
});

// ─── store ────────────────────────────────────────────────────────────────────

it('creates new content through store endpoint when authenticated', function (): void {
    $user = makeUserForZennerTest();

    $this->mock(ContentService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('storeContent')
            ->once()
            ->andReturn([
                'id' => 10,
                'title' => 'Materi Baru',
                'slug' => 'materi-baru',
                'status' => 'published',
            ]);
    });

    $this->actingAs($user, 'sanctum')
        ->postJson(route('api.zenner-academy.contents.store'), [
            'title' => 'Materi Baru',
            'status' => 'published',
        ])
        ->assertCreated()
        ->assertJsonPath('message', 'Konten berhasil dibuat.')
        ->assertJsonPath('data.id', 10)
        ->assertJsonPath('data.title', 'Materi Baru');
});

it('rejects store request without required fields', function (): void {
    $user = makeUserForZennerTest();

    $this->actingAs($user, 'sanctum')
        ->postJson(route('api.zenner-academy.contents.store'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'status']);
});

it('requires authentication for store endpoint', function (): void {
    $this->postJson(route('api.zenner-academy.contents.store'), [
        'title' => 'Konten Test',
        'status' => 'published',
    ])->assertUnauthorized();
});

// ─── update ───────────────────────────────────────────────────────────────────

it('updates content through update endpoint when authenticated', function (): void {
    $user = makeUserForZennerTest();

    $this->mock(ContentService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('updateContent')
            ->once()
            ->with(5, ['title' => 'Materi Diperbarui', 'status' => 'draft'])
            ->andReturn(['id' => 5, 'title' => 'Materi Diperbarui', 'status' => 'draft']);
    });

    $this->actingAs($user, 'sanctum')
        ->putJson(route('api.zenner-academy.contents.update', ['content' => 5]), [
            'title' => 'Materi Diperbarui',
            'status' => 'draft',
        ])
        ->assertOk()
        ->assertJsonPath('message', 'Konten berhasil diperbarui.')
        ->assertJsonPath('data.title', 'Materi Diperbarui');
});

it('returns 404 on update when content id does not exist', function (): void {
    $user = makeUserForZennerTest();

    $this->mock(ContentService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('updateContent')
            ->once()
            ->andThrow(new ModelNotFoundException);
    });

    $this->actingAs($user, 'sanctum')
        ->putJson(route('api.zenner-academy.contents.update', ['content' => 999]), [
            'title' => 'Konten Test',
        ])
        ->assertNotFound()
        ->assertJsonPath('message', 'Konten tidak ditemukan.');
});

it('requires authentication for update endpoint', function (): void {
    $this->putJson(route('api.zenner-academy.contents.update', ['content' => 1]), [
        'title' => 'Test',
    ])->assertUnauthorized();
});

// ─── destroy ──────────────────────────────────────────────────────────────────

it('deletes content through destroy endpoint when authenticated', function (): void {
    $user = makeUserForZennerTest();

    $this->mock(ContentService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('deleteContent')
            ->once()
            ->with(5);
    });

    $this->actingAs($user, 'sanctum')
        ->deleteJson(route('api.zenner-academy.contents.destroy', ['content' => 5]))
        ->assertOk()
        ->assertJsonPath('message', 'Konten berhasil dihapus.');
});

it('returns 404 on destroy when content id does not exist', function (): void {
    $user = makeUserForZennerTest();

    $this->mock(ContentService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('deleteContent')
            ->once()
            ->andThrow(new ModelNotFoundException);
    });

    $this->actingAs($user, 'sanctum')
        ->deleteJson(route('api.zenner-academy.contents.destroy', ['content' => 999]))
        ->assertNotFound()
        ->assertJsonPath('message', 'Konten tidak ditemukan.');
});

it('requires authentication for destroy endpoint', function (): void {
    $this->deleteJson(route('api.zenner-academy.contents.destroy', ['content' => 1]))
        ->assertUnauthorized();
});

// ─── byCategory ───────────────────────────────────────────────────────────────

it('returns content list by category slug', function (): void {
    $this->mock(ContentService::class, function (MockInterface $mock): void {
        $paginator = new LengthAwarePaginator(
            items: [['id' => 3, 'title' => 'Konten Academy', 'status' => 'published']],
            total: 1,
            perPage: 15,
            currentPage: 1,
        );

        $mock->shouldReceive('getContentByCategory')
            ->once()
            ->with('academy', ['search' => null, 'status' => null], 15)
            ->andReturn([
                'category' => ['id' => 1, 'name' => 'Academy', 'slug' => 'academy'],
                'contents' => $paginator,
            ]);
    });

    $this->getJson(route('api.zenner-academy.categories.contents', ['categorySlug' => 'academy']))
        ->assertOk()
        ->assertJsonPath('message', 'Daftar konten berhasil diambil.')
        ->assertJsonPath('data.category.slug', 'academy')
        ->assertJsonPath('data.contents.data.0.title', 'Konten Academy');
});

it('returns 404 when category slug for content list does not exist', function (): void {
    $this->mock(ContentService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getContentByCategory')
            ->once()
            ->andThrow(new ModelNotFoundException);
    });

    $this->getJson(route('api.zenner-academy.categories.contents', ['categorySlug' => 'tidak-ada']))
        ->assertNotFound()
        ->assertJsonPath('message', 'Kategori tidak ditemukan.');
});
