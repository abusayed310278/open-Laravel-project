<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogCommentController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status', 'all');
        $search = $request->query('search');

        $query = BlogComment::query()
            ->with(['post', 'user'])
            ->latest();

        if ($statusFilter !== 'all' && in_array($statusFilter, ['approved', 'pending', 'spam'], true)) {
            $query->where('status', $statusFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('author_name', 'like', "%{$search}%")
                    ->orWhere('author_email', 'like', "%{$search}%")
                    ->orWhere('comment', 'like', "%{$search}%")
                    ->orWhereHas('post', fn ($pq) => $pq->where('title', 'like', "%{$search}%"));
            });
        }

        $comments = $query->paginate(20)->withQueryString();

        $counts = [
            'all' => BlogComment::count(),
            'approved' => BlogComment::where('status', 'approved')->count(),
            'pending' => BlogComment::where('status', 'pending')->count(),
            'spam' => BlogComment::where('status', 'spam')->count(),
        ];

        return view('admin.posts.comments', compact('comments', 'counts', 'statusFilter', 'search'));
    }

    public function approve(BlogComment $comment): RedirectResponse
    {
        $comment->update(['status' => 'approved']);

        return back()->with('status', 'Comment approved.');
    }

    public function pending(BlogComment $comment): RedirectResponse
    {
        $comment->update(['status' => 'pending']);

        return back()->with('status', 'Comment marked as pending.');
    }

    public function spam(BlogComment $comment): RedirectResponse
    {
        $comment->update(['status' => 'spam']);

        return back()->with('status', 'Comment marked as spam.');
    }

    public function destroy(BlogComment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('status', 'Comment deleted.');
    }
}
