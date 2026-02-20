<?php

return [
    'roles' => [
        'root',
        'admin',
        'customer',
        'visitor'
    ],
    'permissions' => [
        'root' => ['root', 'admin', 'visitor'],

        'service.index' => ['root', 'admin', 'visitor'],
        'service.create' => ['root', 'admin', 'visitor'],
        'service.store' => ['root', 'admin', 'visitor'],
        'service.edit' => ['root', 'admin', 'visitor'],
        'service.update' => ['root', 'admin', 'visitor'],
        'service.status.toggle' => ['root', 'admin', 'visitor'],

        'additional.index' => ['root', 'admin', 'visitor'],
        'additional.create' => ['root', 'admin', 'visitor'],
        'additional.store' => ['root', 'admin', 'visitor'],
        'additional.edit' => ['root', 'admin', 'visitor'],
        'additional.update' => ['root', 'admin', 'visitor'],
        'additional.status.toggle' => ['root'],

        'variant.index' => ['root', 'admin', 'visitor'],
        'variant.create' => ['root', 'admin', 'visitor'],
        'variant.edit' => ['root', 'admin', 'visitor'],
        'variant.update' => ['root', 'admin', 'visitor'],
        'variant.store' => ['root', 'admin', 'visitor'],
        'variant.products' => ['root', 'admin', 'visitor'],

        'notification.index' => ['root', 'admin', 'visitor'],
        'notification.send' => ['root', 'admin', 'visitor'],

        'customer.index' => ['root', 'admin', 'visitor'],
        'customer.show' => ['root', 'admin', 'visitor'],
        'customer.create' => ['root', 'admin', 'visitor'],
        'customer.store' => ['root', 'admin', 'visitor'],
        'customer.edit' => ['root', 'admin', 'visitor'],
        'customer.update' => ['root', 'admin', 'visitor'],

        'product.index' => ['root', 'admin', 'visitor'],
        'product.create' => ['root', 'admin', 'visitor'],
        'product.store' => ['root', 'admin', 'visitor'],
        'product.show' => ['root', 'admin', 'visitor'],
        'product.edit' => ['root', 'admin', 'visitor'],
        'product.update' => ['root', 'admin', 'visitor'],
        'product.status.toggle' => ['root'],

        'banner.index' => ['root', 'admin', 'visitor'],
        'banner.promotional' => ['root', 'admin', 'visitor'],
        'banner.store' => ['root', 'admin', 'visitor'],
        'banner.edit' => ['root', 'admin', 'visitor'],
        'banner.update' => ['root', 'admin', 'visitor'],
        'banner.destroy' => ['root', 'admin', 'visitor'],
        'banner.status.toggle' => ['root'],

        'order.index' => ['root', 'admin', 'visitor'],
        'order.show' => ['root', 'admin', 'visitor'],
        'order.status.change' => ['root', 'admin', 'visitor'],
        'order.print.labels' => ['root', 'admin', 'visitor'],
        'order.print.invioce' => ['root', 'admin', 'visitor'],
        'orderIncomplete.index' => ['root', 'admin', 'visitor'],
        'orderIncomplete.paid' => ['root'],

        'revenue.index' => ['root', 'admin', 'visitor'],
        'revenue.generate.pdf' => ['root', 'admin', 'visitor'],
        'report.generate.pdf' => ['root', 'admin', 'visitor'],

        'coupon.index' => ['root', 'admin', 'visitor'],
        'coupon.create' => ['root', 'admin', 'visitor'],
        'coupon.store' => ['root', 'admin', 'visitor'],
        'coupon.edit' => ['root', 'admin', 'visitor'],
        'coupon.update' => ['root', 'admin', 'visitor'],

        'contact' => ['root', 'admin', 'visitor'],

        'profile.index' => ['root', 'admin', 'visitor'],
        'profile.update' => ['root', 'admin', 'visitor'],
        'profile.edit' => ['root', 'admin', 'visitor'],
        'profile.change-password' => ['root', 'admin', 'visitor'],

        'schedule.index' => ['root', 'admin', 'visitor'],
        'toggole.status.update' => ['root'],
        'schedule.update' => ['root', 'admin', 'visitor'],

        'dashboard.calculation' => ['root', 'admin', 'visitor'],
        'dashboard.revenue' => ['root', 'admin', 'visitor'],
        'dashboard.overview' => ['root', 'admin', 'visitor'],

        'setting.show' => ['root', 'admin', 'visitor'],
        'setting.edit' => ['root', 'admin', 'visitor'],
        'setting.update' => ['root', 'admin', 'visitor'],

        'sms-gateway.index' => ['root', 'admin', 'visitor'],
        'sms-gateway.update' => ['root', 'admin', 'visitor'],

        'admin.index' => ['root', 'admin', 'visitor'],
        'admin.status-update' => ['root'],
        'admin.create' => ['root', 'admin', 'visitor'],
        'admin.store' => ['root'],
        'admin.edit' => ['root', 'admin', 'visitor'],
        'admin.update' => ['root'],
        'admin.show' => ['root', 'admin', 'visitor'],
        'admin.set-permission' => ['root'],

        'service-areas.index' => ['root', 'admin', 'visitor'],
        'service-areas.store' => ['root', 'admin', 'visitor'],
        'service-areas.update' => ['root', 'admin', 'visitor'],
        'service-areas.toggle' => ['root', 'admin', 'visitor'],
        'service-areas.delete' => ['root', 'admin', 'visitor'],
    ],
];
