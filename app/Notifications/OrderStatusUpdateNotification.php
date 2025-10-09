<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdateNotification extends Notification
{
    use Queueable;

    public Order $order;

    public string $previousStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, string $previousStatus)
    {
        $this->order = $order;
        $this->previousStatus = $previousStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $orderUrl = url(config('app.frontend_url', config('app.url')).'/orders/'.$this->order->id);
        $statusMessage = $this->getStatusMessage();

        return (new MailMessage)
            ->subject('Order Status Update - Order #'.$this->order->id)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Your order status has been updated.')
            ->line('**Order Details:**')
            ->line('Order ID: #'.$this->order->id)
            ->line('Previous Status: '.ucfirst($this->previousStatus))
            ->line('Current Status: '.ucfirst($this->order->status))
            ->line($statusMessage)
            ->action('Track Your Order', $orderUrl)
            ->line('Thank you for your business!');
    }

    /**
     * Get status-specific message
     */
    private function getStatusMessage(): string
    {
        return match ($this->order->status) {
            'processing' => 'Great news! Your order is now being processed and will be prepared for shipment soon.',
            'shipped' => 'Excellent! Your order has been shipped and is on its way to you. You should receive it within 3-5 business days.',
            'delivered' => 'Your order has been delivered! We hope you love your purchase. Please let us know if you have any feedback.',
            'cancelled' => 'Your order has been cancelled. If this was unexpected, please contact our customer service team.',
            default => 'Your order status has been updated. Please check your account for more details.'
        };
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'previous_status' => $this->previousStatus,
            'current_status' => $this->order->status,
            'message' => 'Order #'.$this->order->id.' status changed from '.$this->previousStatus.' to '.$this->order->status,
            'updated_at' => now()->toISOString(),
        ];
    }
}
