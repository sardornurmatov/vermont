<?php

return [
    'status' => [
        'waiting_payment' => 'Waiting for payment',
        'preparing' => 'Order is being prepared',
        'assigned' => 'Courier assigned',
        'picked_up' => 'Courier picked up the order',
        'on_the_way' => 'Order is on the way',
        'delivered' => 'Delivered',
        'delivery_failed' => 'Delivery failed',
    ],
    'failed_notice' => 'Delivery was unsuccessful. The shop will contact you again.',
    'ui' => [
        'delivery' => 'Delivery', 'eta' => 'Estimated delivery time', 'courier' => 'Courier', 'courier_orders' => 'Courier orders',
        'courier_contact_note' => 'The courier will contact you about the order.', 'waiting_assignment' => 'After payment is confirmed, your order will be prepared and assigned to a courier.',
        'address' => 'Address', 'note' => 'Note', 'courier_note' => 'Note for the courier (optional)',
        'courier_note_placeholder' => 'Landmark, entrance, or delivery instructions',
        'products' => 'Products', 'delivery_fee' => 'Delivery', 'free_or_unset' => 'Free / rate not configured',
        'grand_total' => 'Total due', 'order_details' => 'Order details', 'payment_details' => 'Payment details',
        'delivery_tracking' => 'Order tracking', 'admin_assign' => 'Courier account email',
        'admin_eta' => 'Estimated delivery time', 'assign_button' => 'Assign courier',
        'role_notice' => 'Selecting an existing customer changes their role to courier.',
        'customer_track' => 'View and track order', 'courier_panel' => 'Courier panel',
        'courier_title' => 'Orders assigned to me', 'courier_step' => 'Update delivery status',
        'save_status' => 'Save status', 'finished' => 'Delivery for this order is complete.',
        'no_orders' => 'No orders have been assigned to you yet.', 'validation_sequence' => 'Check the delivery status sequence.',
        'payment_first' => 'Confirm payment first.', 'admin_not_courier' => 'An administrator cannot be assigned as a courier.',
        'assigned_success' => 'Courier assigned to the order.', 'update_success' => 'Delivery status updated.',
        'payment_save' => 'Save payment status', 'courier_note_admin' => 'Note for courier:',
    ],
];
