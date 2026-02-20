# صلاحيات الشريط الجانبي (Sidebar Permissions)

الملف: `resources/views/layouts/partials/sidebar.blade.php`

## خيارات تظهر دائماً (بدون صلاحية)
| الخيار        | ملاحظة |
|---------------|--------|
| Admin Logo   | الرابط للوحة التحكم |
| Dashboard    | بدون `@can` |
| POS Manage   | بدون `@can` |
| Subscriptions| بدون `@can` |
| language     | بدون شرط |
| Logout       | بدون شرط |

---

## خيارات تحتاج صلاحية (Permission)
يجب أن يملك المستخدم الصلاحية التالية في `config/acl.php` ودورهم مضاف لها.

| الخيار        | الصلاحية المطلوبة |
|---------------|-------------------|
| **Orders**    | `order.index` |
| **Product Manage** | واحدة على الأقل من: `product.index` أو `coupon.index` أو `variant.index` أو `service.index` |
| **Notifications**  | `notification.index` |
| **Reports**       | `revenue.index` |
| **App Banners**    | `banner.promotional` |
| **Customer**      | `customer.index` |
| **Contacts**      | `contact` |
| **Profile**       | `profile.index` |

---

## خيارات تحتاج دور (Role)
يجب أن يكون للمستخدم أحد الأدوار: **root** أو **admin** أو **visitor**.

| الخيار   | الشرط في الكود |
|----------|-----------------|
| **Admins**  | `@role('root|admin|visitor')` |
| **Settings**| داخل نفس `@role('root|admin|visitor')` |

---

## ملخص للمستخدم root أو admin
- إضافة دور المستخدم في `config/acl.php` لكل الصلاحيات أعلاه (مصفوفة الأدوار تحتوي على `root` و/أو `admin` و/أو `visitor`).
- تشغيل `php artisan db:seed --class=RolePermissionSeeder` لمزامنة الصلاحيات مع الأدوار والمستخدمين.
- تشغيل `php artisan permission:cache-reset` ثم تسجيل الخروج والدخول من جديد.
