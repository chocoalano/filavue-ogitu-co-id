<?php

use App\Models\User;
use App\Services\ZennerAcademy\ContentCategoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery\MockInterface;

function makeUserForZennerCategoryTest(int $id = 1): User
{
    $user = new User;
    $user->forceFill([
        'name' => 'Admin Test',
        'email' => 'admin.cat'.$id.'@example.test',
        'password' => bcrypt('password'),
    ]);
    $user->setAttribute('id', $id);
    $user->exists = true;

    return $user;
}

// ─── Route registration ───────────────────────────────────────────────────────

it('registers zenner academy category routes', function (): void {
    expect(route('api.zenner-academy.categories.index', [], false))->toBe('/api/zenner-academy/categories')
        ->and(route('api.zenner-academy.categories.parents', [], false))->toBe('/api/zenner-academy/categories/parents')
        ->and(route('api.zenner-academy.categories.show', ['category' => 1], false))->toBe('/api/zenner-academy/categories/1')
        ->and(route('api.zenner-academy.categories.show-by-slug', ['slug' => 'academy'], false))->toBe('/api/zenner-academy/categories/slug/academy')
        ->and(route('api.zenner-academy.categories.store', [], false))->toBe('/api/zenner-academy/categories')
        ->and(route('api.zenner-academy.categories.update', ['category' => 1], false))->toBe('/api/zenner-academy/categories/1')
        ->and(route('api.zenner-academy.categories.destroy', ['category' => 1], false))->toBe('/api/zenner-academy/categories/1');
});

// ─── index ────────────────────────────────────────────────────────────────────

it('returns full category tree from index endpoint', function (): void {
    $this->mock(ContentCategoryService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getCategoryTree')
            ->once()
            ->andReturn([
                ['id' => 1, 'name' => 'Academy', 'slug' => 'academy', 'children' => [
                    ['id' => 3, 'name' => 'Sub Academy', 'slug' => 'sub-academy', 'children' => []],
                ]],
                ['id' => 2, 'name' => 'Marketing Kit', 'slug' => 'marketing-kit', 'children' => []],
            ]);
    });

    $this->getJson(route('api.zenner-academy.categories.index'))
        ->assertOk()
        ->assertJsonPath('message', 'Daftar kategori berhasil diambil.')
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.slug', 'academy')
        ->assertJsonPath('data.0.children.0.slug', 'sub-academy');
});

it('returns only parent categories when parents_only query is true', function (): void {
    $this->mock(ContentCategoryService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getParentCategories')
            ->once()
            ->andReturn([
                ['id' => 1, 'name' => 'Academy', 'slug' => 'academy', 'parent_id' => null, 'children' => []],
            ]);
    });

    $this->getJson(route('api.zenner-academy.categories.index').'?parents_only=1')
        ->assertOk()
        ->assertJsonPath('data.0.parent_id', null);
});

// ─── parents ─────────────────────────────────────────────────────────────────

it('returns parent categories from dedicated parents endpoint', function (): void {
    $this->mock(ContentCategoryService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getParentCategories')
            ->once()
            ->andReturn([
                ['id' => 1, 'name' => 'Academy', 'slug' => 'academy', 'parent_id' => null, 'children' => []],
                ['id' => 2, 'name' => 'Marketing Kit', 'slug' => 'marketing-kit', 'parent_id' => null, 'children' => []],
            ]);
    });

    $this->getJson(route('api.zenner-academy.categories.parents'))
        ->assertOk()
        ->assertJsonPath('message', 'Daftar kategori induk berhasil diambil.')
        ->assertJsonCount(2, 'data');
});

it('returns sub-categories with parent info when parent id is given', function (): void {
    $this->mock(ContentCategoryService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getSubCategoriesByParent')
            ->once()
            ->with(1)
            ->andReturn([
                'parent' => ['id' => 1, 'name' => 'Academy', 'slug' => 'academy', 'parent_id' => null, 'contents_count' => 0],
                'children' => [
                    ['id' => 3, 'name' => 'Sub Academy', 'slug' => 'sub-academy', 'parent_id' => 1, 'children' => []],
                ],
            ]);
    });

    $this->getJson(route('api.zenner-academy.categories.parents').'?parent=1')
        ->assertOk()
        ->assertJsonPath('message', 'Daftar sub-kategori berhasil diambil.')
        ->assertJsonPath('data.parent.id', 1)
        ->assertJsonPath('data.parent.name', 'Academy')
        ->assertJsonPath('data.children.0.slug', 'sub-academy');
});

it('returns 404 when given parent id does not exist', function (): void {
    $this->mock(ContentCategoryService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getSubCategoriesByParent')
            ->once()
            ->with(999)
            ->andThrow(new ModelNotFoundException);
    });

    $this->getJson(route('api.zenner-academy.categories.parents').'?parent=999')
        ->assertNotFound()
        ->assertJsonPath('message', 'Kategori tidak ditemukan.');
});

// ─── show ─────────────────────────────────────────────────────────────────────

it('returns category detail from show endpoint', function (): void {
    $this->mock(ContentCategoryService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getCategoryDetail')
            ->once()
            ->with(1)
            ->andReturn([
                'id' => 1,
                'name' => 'Academy',
                'slug' => 'academy',
                'parent_id' => null,
                'contents_count' => 5,
                'contents' => [
                    ['id' => 10, 'title' => 'Konten A', 'slug' => 'konten-a'],
                    ['id' => 11, 'title' => 'Konten B', 'slug' => 'konten-b'],
                ],
                'children' => [],
            ]);
    });

    $this->getJson(route('api.zenner-academy.categories.show', ['category' => 1]))
        ->assertOk()
        ->assertJsonPath('message', 'Detail kategori berhasil diambil.')
        ->assertJsonPath('data.id', 1)
        ->assertJsonPath('data.name', 'Academy')
        ->assertJsonPath('data.contents_count', 5)
        ->assertJsonPath('data.contents.0.id', 10)
        ->assertJsonPath('data.contents.0.slug', 'konten-a')
        ->assertJsonCount(2, 'data.contents');
});

it('returns 404 when category id does not exist', function (): void {
    $this->mock(ContentCategoryService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getCategoryDetail')
            ->once()
            ->with(999)
            ->andThrow(new ModelNotFoundException);
    });

    $this->getJson(route('api.zenner-academy.categories.show', ['category' => 999]))
        ->assertNotFound()
        ->assertJsonPath('message', 'Kategori tidak ditemukan.');
});

it('returns category detail by slug', function (): void {
    $this->mock(ContentCategoryService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getCategoryDetailBySlug')
            ->once()
            ->with('academy')
            ->andReturn([
                'id' => 1,
                'name' => 'Academy',
                'slug' => 'academy',
                'contents' => [
                    ['id' => 5, 'title' => 'Konten Lima', 'slug' => 'konten-lima'],
                    ['id' => 7, 'title' => 'Konten Tujuh', 'slug' => 'konten-tujuh'],
                ],
                'children' => [],
            ]);
    });

    $this->getJson(route('api.zenner-academy.categories.show-by-slug', ['slug' => 'academy']))
        ->assertOk()
        ->assertJsonPath('data.slug', 'academy')
        ->assertJsonPath('data.contents.0.id', 5)
        ->assertJsonPath('data.contents.1.slug', 'konten-tujuh')
        ->assertJsonCount(2, 'data.contents');
});

it('returns 404 when category slug does not exist', function (): void {
    $this->mock(ContentCategoryService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('getCategoryDetailBySlug')
            ->once()
            ->andThrow(new ModelNotFoundException);
    });

    $this->getJson(route('api.zenner-academy.categories.show-by-slug', ['slug' => 'slug-tidak-ada']))
        ->assertNotFound()
        ->assertJsonPath('message', 'Kategori tidak ditemukan.');
});

// ─── store ────────────────────────────────────────────────────────────────────

it('creates new category through store endpoint when authenticated', function (): void {
    $user = makeUserForZennerCategoryTest();

    $this->mock(ContentCategoryService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('storeCategory')
            ->once()
            ->andReturn([
                'id' => 5,
                'name' => 'Kategori Baru',
                'slug' => 'kategori-baru',
                'parent_id' => null,
                'children' => [],
            ]);
    });

    $this->actingAs($user, 'sanctum')
        ->postJson(route('api.zenner-academy.categories.store'), [
            'name' => 'Kategori Baru',
        ])
        ->assertCreated()
        ->assertJsonPath('message', 'Kategori berhasil dibuat.')
        ->assertJsonPath('data.name', 'Kategori Baru')
        ->assertJsonPath('data.slug', 'kategori-baru');
});

it('rejects store request without required name field', function (): void {
    $user = makeUserForZennerCategoryTest();

    $this->actingAs($user, 'sanctum')
        ->postJson(route('api.zenner-academy.categories.store'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

it('requires authentication for category store endpoint', function (): void {
    $this->postJson(route('api.zenner-academy.categories.store'), [
        'name' => 'Kategori Test',
    ])->assertUnauthorized();
});

// ─── update ───────────────────────────────────────────────────────────────────

it('updates category through update endpoint when authenticated', function (): void {
    $user = makeUserForZennerCategoryTest();

    $this->mock(ContentCategoryService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('updateCategory')
            ->once()
            ->with(1, ['name' => 'Nama Diperbarui'])
            ->andReturn(['id' => 1, 'name' => 'Nama Diperbarui', 'slug' => 'academy', 'children' => []]);
    });

    $this->actingAs($user, 'sanctum')
        ->putJson(route('api.zenner-academy.categories.update', ['category' => 1]), [
            'name' => 'Nama Diperbarui',
        ])
        ->assertOk()
        ->assertJsonPath('message', 'Kategori berhasil diperbarui.')
        ->assertJsonPath('data.name', 'Nama Diperbarui');
});

it('returns 404 on update when category id does not exist', function (): void {
    $user = makeUserForZennerCategoryTest();

    $this->mock(ContentCategoryService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('updateCategory')
            ->once()
            ->andThrow(new ModelNotFoundException);
    });

    $this->actingAs($user, 'sanctum')
        ->putJson(route('api.zenner-academy.categories.update', ['category' => 999]), [
            'name' => 'Test',
        ])
        ->assertNotFound()
        ->assertJsonPath('message', 'Kategori tidak ditemukan.');
});

it('requires authentication for category update endpoint', function (): void {
    $this->putJson(route('api.zenner-academy.categories.update', ['category' => 1]), [
        'name' => 'Test',
    ])->assertUnauthorized();
});

// ─── destroy ──────────────────────────────────────────────────────────────────

it('deletes category through destroy endpoint when authenticated', function (): void {
    $user = makeUserForZennerCategoryTest();

    $this->mock(ContentCategoryService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('deleteCategory')
            ->once()
            ->with(1);
    });

    $this->actingAs($user, 'sanctum')
        ->deleteJson(route('api.zenner-academy.categories.destroy', ['category' => 1]))
        ->assertOk()
        ->assertJsonPath('message', 'Kategori berhasil dihapus.');
});

it('returns 404 on destroy when category id does not exist', function (): void {
    $user = makeUserForZennerCategoryTest();

    $this->mock(ContentCategoryService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('deleteCategory')
            ->once()
            ->andThrow(new ModelNotFoundException);
    });

    $this->actingAs($user, 'sanctum')
        ->deleteJson(route('api.zenner-academy.categories.destroy', ['category' => 999]))
        ->assertNotFound()
        ->assertJsonPath('message', 'Kategori tidak ditemukan.');
});

it('requires authentication for category destroy endpoint', function (): void {
    $this->deleteJson(route('api.zenner-academy.categories.destroy', ['category' => 1]))
        ->assertUnauthorized();
});
