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
use App\Mail\Concerns\SendsCsMail;

class SendBlogNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SendsCsMail;

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
            // Signed so the link can't be used to unsubscribe an arbitrary email —
            // BlogController::unsubscribe rejects any request without a valid signature.
            $unsubscribeUrl = \Illuminate\Support\Facades\URL::signedRoute(
                'blog.unsubscribe',
                ['email' => $this->subscriber->email]
            );
            
            $appName        = getOption('app_name') ?: config('app.name');
            $subscriberName = e($this->subscriber->name ?: __('there'));
            $postTitle      = e($this->post->title);
            $authorName     = e($this->post->author->name ?? __('Admin'));
            $readingTime    = e($this->post->reading_time_text ?? '');
            $excerpt        = e($this->post->excerpt ?? '');

            $blocks = [];
            if ($this->post->featured_image) {
                $imageUrl = asset('storage/' . $this->post->featured_image);
                $blocks[] = ['type' => 'text', 'html' =>
                    '<img src="' . e($imageUrl) . '" alt="' . $postTitle . '" style="width:100%;max-width:532px;height:auto;border-radius:10px;">'];
            }
            $blocks[] = ['type' => 'text', 'html' => __('Hello :name,', ['name' => "<strong>{$subscriberName}</strong>"])
                . ' ' . __('A new article has been published on the :app blog.', ['app' => e($appName)])];
            $meta = trim($authorName . ($readingTime ? ' · ' . $readingTime : ''));
            $blocks[] = ['type' => 'text', 'html' => '<strong style="font-size:16px;color:#1F2A37;">' . $postTitle . '</strong>'
                . ($meta ? "<br><span style='color:#8A97A8;font-size:12.5px;'>" . $meta . '</span>' : '')
                . ($excerpt ? "<br><br><span style='color:#6b7280;'>" . $excerpt . '</span>' : '')];
            $blocks[] = ['type' => 'button', 'url' => $postUrl, 'label' => __('Read full article')];

            $this->sendCs(
                [$this->subscriber->email],
                __('New article: :title', ['title' => $this->post->title]),
                [
                    'eyebrow'  => __('New article'), 'eyebrowColor' => '#185FA5',
                    'title'    => __('New article published'),
                    'blocks'   => $blocks,
                    'footnote' => __("You're receiving this because you subscribed to the :app blog.", ['app' => $appName])
                        . ' <a href="' . e($unsubscribeUrl) . '" style="color:#8A97A8;">' . __('Unsubscribe') . '</a>',
                ]
            );

        } catch (\Exception $e) {
            Log::error('SendBlogNotificationJob failed for subscriber ' . $this->subscriber->email . ': ' . $e->getMessage(), [
                'post_id' => $this->post->id,
                'subscriber_id' => $this->subscriber->id,
            ]);
        }
    }
}