<?php

namespace App\Jobs;

use App\Models\BlogPost;
use App\Models\BlogSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBlogNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public BlogPost $post,
        public BlogSubscriber $subscriber
    ) {}

    public function handle(): void
    {
        try {
            if (!$this->subscriber->is_active || !$this->subscriber->email) {
                return;
            }

            $postUrl = route('blog.show', $this->post->slug);
            $unsubscribeUrl = route('blog.unsubscribe', ['email' => $this->subscriber->email]);
            
            Mail::send([], [], function ($message) use ($postUrl, $unsubscribeUrl) {
                $message->to($this->subscriber->email)
                        ->subject('New Article: ' . $this->post->title)
                        ->html($this->buildEmailHtml($postUrl, $unsubscribeUrl));
            });

        } catch (\Exception $e) {
            Log::error('SendBlogNotificationJob failed for subscriber ' . $this->subscriber->email . ': ' . $e->getMessage(), [
                'post_id' => $this->post->id,
                'subscriber_id' => $this->subscriber->id,
            ]);
        }
    }

    protected function buildEmailHtml($postUrl, $unsubscribeUrl): string
    {
        $postTitle = e($this->post->title);
        $postExcerpt = e($this->post->excerpt ?? '');
        $authorName = e($this->post->author->name ?? 'Admin');
        $readingTime = $this->post->reading_time_text;
        $subscriberName = e($this->subscriber->name ?? 'Subscriber');
        $appName = e(config('app.name'));
        
        $featuredImageHtml = '';
        if ($this->post->featured_image) {
            $imageUrl = asset('storage/' . $this->post->featured_image);
            $featuredImageHtml = '<img src="' . $imageUrl . '" alt="' . $postTitle . '" style="width: 100%; max-width: 600px; height: auto; border-radius: 8px; margin-bottom: 20px;">';
        }

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #374151; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #185FA5; color: #fff; padding: 30px; text-align: center; border-radius: 12px 12px 0 0; }
                .content { background: #fff; padding: 30px; border: 0.5px solid #e5e7eb; }
                .button { display: inline-block; background: #185FA5; color: #fff; text-decoration: none; padding: 12px 30px; border-radius: 7px; font-weight: 600; margin: 20px 0; }
                .meta { color: #9ca3af; font-size: 13px; margin-bottom: 20px; }
                .footer { background: #fafafa; padding: 20px 30px; text-align: center; border-radius: 0 0 12px 12px; border: 0.5px solid #e5e7eb; font-size: 12px; color: #9ca3af; }
                .unsubscribe { color: #9ca3af; text-decoration: underline; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1 style="margin: 0; font-size: 24px;">📢 New Article Published</h1>
                </div>
                <div class="content">
                    <p>Hello ' . $subscriberName . ',</p>
                    <p>A new article has been published on the ' . $appName . ' blog:</p>
                    
                    ' . $featuredImageHtml . '
                    
                    <h2 style="color: #111827; font-size: 20px; margin-bottom: 12px;">' . $postTitle . '</h2>
                    
                    <div class="meta">
                        By ' . $authorName . ' · ' . $readingTime . '
                    </div>
                    
                    ' . ($postExcerpt ? '<p style="color: #6b7280;">' . $postExcerpt . '</p>' : '') . '
                    
                    <a href="' . $postUrl . '" class="button">Read Full Article →</a>
                </div>
                <div class="footer">
                    <p>You\'re receiving this email because you subscribed to the ' . $appName . ' blog.</p>
                    <p><a href="' . $unsubscribeUrl . '" class="unsubscribe">Unsubscribe from these emails</a></p>
                </div>
            </div>
        </body>
        </html>';
    }
}