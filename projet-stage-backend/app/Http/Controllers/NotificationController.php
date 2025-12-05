<?php
namespace App\Http\Controllers;

use App\Events\PostThresholdExceeded;
use App\Models\Notification;
use App\Models\Posts;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function checkPostThresholds()
    {
        $thresholdLikes = 0;
        $thresholdComments = 20;

        try {
            $posts = Posts::all(); 

            foreach ($posts as $post) {
                $likesCount = ($post->likes_count !== 'N' && $post->likes_count !== null) ? intval($post->likes_count) : 0;
                $commentsCount = ($post->comments_count !== 'N' && $post->comments_count !== null) ? intval($post->comments_count) : 0;

                $notification = Notification::firstOrNew(['post_id' => $post->idPost]);
                $likeIcon = '❤️'; 
                $commentIcon = '💬';  
                $hasExceededThreshold = false; 

                if ($likesCount > $thresholdLikes) {
                    $notification->like_message = $likeIcon . ' Post ID ' . $post->idPost . ' has exceeded the like threshold.';
                    $hasExceededThreshold = true;
                }

                if ($commentsCount > $thresholdComments) {
                    $notification->comment_message = $commentIcon . ' Post ID ' . $post->idPost . ' has exceeded the comment threshold.';
                    $hasExceededThreshold = true;
                }

                if ($hasExceededThreshold) {
                    $notification->videoUrl = $post->video_url;
                    $notification->image_url = $post->image_url;

                    if ($notification->isDirty()) {
                        $notification->save();
                        Log::info('Notification created or updated for Post ID: ' . $post->idPost);
                        
                        $urlMessage = $post->video_url ? "Video URL: " . $post->video_url : "Image URL: " . $post->image_url;

                        $eventMessage = ($notification->like_message ?? $notification->comment_message) . ' - ' . $urlMessage;
                        event(new PostThresholdExceeded($eventMessage));
                    }
                }
            }

            Log::info('Post thresholds checked successfully.');
        } catch (\Exception $e) {
            Log::error('Error checking post thresholds: ' . $e->getMessage());
        }
    }
}
