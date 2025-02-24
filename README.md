# 🛍️ Zarinpal Payment Gateway for Bagisto

### 🎉 Introduction

This package adds support for the **Zarinpal** payment gateway in **Bagisto (v2.x and above)**.\
With this package, you can seamlessly integrate **Zarinpal** into your e-commerce store and offer secure online transactions for your customers.

---

## 👥 Installation

Follow these simple steps to install and configure the package:

### 1️⃣ Copy Package

Move the package to the following directory in your Bagisto project:

```
packages/Webkul/Zarinpal
```

### 2️⃣ Register the Package

Open `config/app.php` and add the following line under the `providers` array:

```php
Webkul\Zarinpal\Providers\ZarinpalServiceProvider::class,
```

### 3️⃣ Add Configuration

Edit `Webkul/Admin/src/Config/system.php` and add the following configuration **after** this section:

```php
sales.payment_methods
```

Then, add:

```php
[
    'key'    => 'sales.payment_methods.zarinpal',
    'name'   => 'admin::app.configuration.index.sales.payment-methods.zarinpal',
    'info'   => 'admin::app.configuration.index.sales.payment-methods.zarinpal-info',
    'sort'   => 1,
    'fields' => [
        [
            'name'          => 'title',
            'title'         => 'admin::app.configuration.index.sales.payment-methods.title',
            'type'          => 'text',
            'depends'       => 'active:1',
            'validation'    => 'required_if:active,1',
            'channel_based' => true,
            'locale_based'  => true,
        ],
        [
            'name'          => 'image',
            'title'         => 'admin::app.configuration.index.sales.payment-methods.logo',
            'type'          => 'image',
            'info'          => 'admin::app.configuration.index.sales.payment-methods.logo-information',
            'channel_based' => true,
            'locale_based'  => false,
            'validation'    => 'mimes:bmp,jpeg,jpg,png,webp',
        ],
        [
            'name'       => 'merchant_id',
            'title'      => 'zarinpal::app.zarinpal.admin.payment_config.title',
            'info'       => 'zarinpal::app.zarinpal.admin.payment_config.title_description',
            'type'       => 'text',
            'depends'    => 'active:1',
            'validation' => 'required_if:active,1',
        ],
        [
            'name'       => 'api_base_url',
            'title'      => 'zarinpal::app.zarinpal.admin.payment_config.api_base_url',
            'type'       => 'text',
            'depends'    => 'active:1',
            'validation' => 'required_if:active,1',
        ],
        [
            'name'          => 'active',
            'title'         => 'admin::app.configuration.index.sales.payment-methods.status',
            'type'          => 'boolean',
            'validation'    => 'required',
            'channel_based' => false,
            'locale_based'  => true,
        ],
        [
            'name'          => 'sandbox',
            'title'         => 'admin::app.configuration.index.sales.payment-methods.sandbox',
            'type'          => 'boolean',
            'channel_based' => false,
            'locale_based'  => true,
        ],
    ],
],
```

### 4️⃣ Run Migrations

Run the following command to update the database schema:

```sh
php artisan migrate
```

### 5️⃣ Clear Config Cache

Clear the configuration cache to apply the changes:

```sh
php artisan optimize:clear
```

### 6️⃣ Configure Zarinpal

Go to **Admin Panel** → **Configuration** → **Sales** → **Payment Methods** and configure your **Zarinpal** settings.

---

## ✅ Features

✨ Supports **Zarinpal** as a payment method in Bagisto\
🔒 Secure transactions using **Merchant ID**\
🖼️ Supports **custom logos** and **descriptions**\
⚙️ Configurable sandbox and production environments

---

## 💡 Need Help?

For any issues, feel free to open an **issue** or contribute to the project. 🚀\
Happy coding! 👨‍💻🎉


## 👥 Contact Info

📧 Email: [hamedslyn@gmail.com](mailto:hamedslyn@gmail.com)\
📢 Telegram: [@hameds0leymani](https://t.me/hameds0leymani)



