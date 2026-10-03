<?php

namespace App\Http\Controllers;

use App\Models\BlogComment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlogPostCommentController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        abort_unless($post->status->value === 'published', 404);

        if (! Auth::check()) {
            return redirect()->guest(route('login'))
                ->with('error', 'Please sign in with your customer account to leave a comment.');
        }

        $user = Auth::user();

        if (! ($user->isCustomer() || $user->isAdmin())) {
            return redirect()->to(url()->previous() . '#comments')
                ->with('comment_error', 'Only customer accounts can leave comments on blog posts.');
        }

        $validated = $request->validate([
            'comment' => ['required', 'string', 'min:3', 'max:3000'],
        ]);

        $comment = new BlogComment([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'author_name' => $user->name,
            'author_email' => $user->email,
            'comment' => $validated['comment'],
            'status' => 'approved',
        ]);

        $comment->save();

        return redirect()->to(url()->previous() . '#comments')
            ->with('comment_status', 'Thank you! Your comment has been posted.');
    }
}
