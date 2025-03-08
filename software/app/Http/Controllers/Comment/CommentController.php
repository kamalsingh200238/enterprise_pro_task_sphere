<?php

namespace App\Http\Controllers\Comment;

use App\Enums\FlashMessageType;
use App\Enums\FlashMessageVariant;
use App\Helpers\FlashMessage;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use Gate;

class CommentController extends Controller
{
    public function delete(Comment $comment)
    {
        // check if user can delete the comment
        Gate::authorize('delete', Comment::class);

        // delete the project
        $deleted = $comment->delete();

        // if project deleted
        if ($deleted) {
            // redirect to show all page with success message
            return back()
                ->with('flash', new FlashMessage(
                    'Comment Deleted Succesfully',
                    FlashMessageVariant::Success,
                    FlashMessageType::Normal,
                )->toArray());
        }

        // if not deleted then send back with flash message
        return back()
            ->with('flash', new FlashMessage(
                'There was a problem in deleting project',
                FlashMessageVariant::Error,
                FlashMessageType::Normal,
            )->toArray());
    }
}
