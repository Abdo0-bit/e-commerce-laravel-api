<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmationNotification extends Notification
{
    use Queueable;

    public Order $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
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

        return (new MailMessage)
            ->subject('Order Confirmation - Order #'.$this->order->id)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Thank you for your order! We have received your order and it is being processed.')
            ->line('**Order Details:**')
            ->line('Order ID: #'.$this->order->id)
            ->line('Order Date: '.$this->order->created_at->format('M d, Y'))
            ->line('Total Amount: $'.number_format($this->order->total_price, 2))
            ->line('Status: '.ucfirst($this->order->status))
            ->action('View Order Details', $orderUrl)
            ->line('We will send you another email when your order ships.')
            ->line('Thank you for shopping with us!');
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
            'total_price' => $this->order->total_price,
            'status' => $this->order->status,
            'message' => 'Your order #'.$this->order->id.' has been confirmed',
            'created_at' => $this->order->created_at->toISOString(),
        ];
    }
}
