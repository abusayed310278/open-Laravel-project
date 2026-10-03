<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BlogService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $author, array $data, ?UploadedFile $image, ?string $tags): Post
    {
        $post = Post::create([
            ...$data,
            'author_id' => $author->id,
            'featured_image' => $image ? \App\Helpers\FileUploadHelper::store($image, 'blog') : null,
        ]);

        $this->syncTags($post, $tags);

        return $post;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Post $post, array $data, ?UploadedFile $image, ?string $tags, bool $removeImage = false): Post
    {
        if ($image) {
            if ($post->featured_image) {
                \App\Support\MediaUrl::delete($post->featured_image);
            }

            $data['featured_image'] = \App\Helpers\FileUploadHelper::store($image, 'blog');
        } elseif ($removeImage && $post->featured_image) {
            \App\Support\MediaUrl::delete($post->featured_image);
            $data['featured_image'] = null;
        }

        $post->update($data);

        $this->syncTags($post, $tags);

        return $post;
    }

    public function delete(Post $post): void
    {
        if ($post->featured_image) {
            \App\Support\MediaUrl::delete($post->featured_image);
        }

        $post->delete();
    }

    private function syncTags(Post $post, ?string $tags): void
    {
        if ($tags === null) {
            return;
        }

        $tagIds = collect(explode(',', $tags))
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->map(fn (string $name) => Tag::query()->firstOrCreate(['name' => $name])->id);

        $post->tags()->sync($tagIds);
    }
}
