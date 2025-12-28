<?php
use App\Models\Utils;

// Ensure company data is available in layout
if (!isset($company)) {
    $company = Utils::company();
}
?>
<!DOCTYPE html>
<html lang="en" prefix="og: http://ogp.me/ns#">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <!-- Primary Meta Tags -->
    <title><?php echo $__env->yieldContent('title', (($company->app_name ?? Utils::app_name()) . ' | School Management System')); ?></title>
    <meta name="title" content="<?php echo $__env->yieldContent('title', (($company->app_name ?? Utils::app_name()) . ' | School Management System')); ?>">
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', (($company->app_name ?? Utils::app_name()) . ' is a comprehensive school management system designed to streamline administrative tasks, enhance communication, and improve efficiency in schools.')); ?>">
    <meta name="keywords" content="<?php echo $__env->yieldContent('meta_keywords', 'school management system, education software, school administration, student management, teacher tools, school communication, online learning, school software, education technology'); ?>">
    <meta name="author" content="<?php echo e($company->name ?? Utils::company_name(), false); ?>">
    <meta name="robots" content="<?php echo $__env->yieldContent('robots', 'index, follow'); ?>">
    <link rel="canonical" href="<?php echo $__env->yieldContent('canonical', url()->current()); ?>">
    
    <!-- Theme Color -->
    <meta name="theme-color" content="<?php echo e($company && $company->primary_color ? $company->primary_color : '#01AEF0', false); ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:type" content="<?php echo $__env->yieldContent('og_type', 'website'); ?>">
    <meta property="og:site_name" content="<?php echo e($company->app_name ?? Utils::app_name(), false); ?>">
    <meta property="og:title" content="<?php echo $__env->yieldContent('og_title', $company->app_name ?? Utils::app_name()); ?>">
    <meta property="og:description" content="<?php echo $__env->yieldContent('og_description', ($company->app_name ?? Utils::app_name()) . ' helps schools manage their operations efficiently with advanced tools and features.'); ?>">
    <meta property="og:image" content="<?php echo $__env->yieldContent('og_image', ($company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo())); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?php echo e($company->name ?? Utils::company_name(), false); ?> Logo">
    <meta property="og:url" content="<?php echo $__env->yieldContent('og_url', url()->current()); ?>">
    <meta property="og:locale" content="en_US">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="{{ $company->twitter_handle ?? Utils::company_name() }}">
    <meta name="twitter:creator" content="{{ $company->twitter_handle ?? Utils::company_name() }}">
    <meta name="twitter:title" content="<?php echo $__env->yieldContent('twitter_title', $company->app_name ?? Utils::app_name()); ?>">
    <meta name="twitter:description" content="<?php echo $__env->yieldContent('twitter_description', ($company->app_name ?? Utils::app_name()) . ' helps schools manage their operations efficiently.'); ?>">
    <meta name="twitter:image" content="<?php echo $__env->yieldContent('twitter_image', ($company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo())); ?>">
    <meta name="twitter:image:alt" content="<?php echo e($company->name ?? Utils::company_name(), false); ?> Logo">
    
    <!-- Additional SEO Meta Tags -->
    <meta name="format-detection" content="telephone=no">
    <meta name="generator" content="<?php echo e($company->app_name ?? Utils::app_name(), false); ?>">
    
    <!-- Favicon and App Icons -->
    <link rel="icon" type="image/x-icon" href="<?php echo e($company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo(), false); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo e($company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo(), false); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo e($company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo(), false); ?>">
    <link rel="apple-touch-icon" href="<?php echo e($company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo(), false); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e($company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo(), false); ?>">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?php echo e(url('manifest.json'), false); ?>">
    
    <!-- iOS Safari Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="<?php echo e($company->app_name ?? Utils::app_name(), false); ?>">
    
    <!-- Microsoft Tiles -->
    <meta name="msapplication-TileColor" content="<?php echo e($company && $company->primary_color ? $company->primary_color : '#01AEF0', false); ?>">
    <meta name="msapplication-config" content="none">
    
    <!-- DNS Prefetch for Performance -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Preload Critical Resources -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"></noscript>
    
    <link rel="preload" href="<?php echo e(asset('css/modern-public.css'), false); ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="<?php echo e(asset('css/modern-public.css'), false); ?>"></noscript>

    <!-- Google Fonts (fallback) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" media="print" onload="this.media='all'">

    <!-- Modern Public CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('css/modern-public.css'), false); ?>">

    <?php if(isset($company) && $company && $company->primary_color && $company->accent_color): ?>
        <!-- Dynamic Company Branding Colors -->
        <style>
            :root {
                --primary-color: <?php echo e($company->primary_color, false); ?> !important;
                --accent-color: <?php echo e($company->accent_color, false); ?> !important;
            }
        </style>
    <?php endif; ?>

    <?php echo $__env->yieldContent('head-styles'); ?>

    <?php echo $__env->yieldPushContent('styles'); ?>
    
    <!-- Schema.org JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "<?php echo e($company->name ?? Utils::company_name(), false); ?>",
        "alternateName": "<?php echo e($company->app_name ?? Utils::app_name(), false); ?>",
        "url": "<?php echo e(url('/'), false); ?>",
        "logo": "<?php echo e($company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo(), false); ?>",
        "description": "<?php echo e($company->app_name ?? Utils::app_name(), false); ?> is a comprehensive school management system designed to streamline administrative tasks, enhance communication, and improve efficiency in schools.",
        "foundingDate": "<?php echo e($company->created_at ?? '2023', false); ?>",
        "email": "<?php echo e($company && $company->email ? $company->email : 'info@newlinetech.com', false); ?>",
        "telephone": "<?php echo e($company && $company->phone ? $company->phone : '+1-555-123-4567', false); ?>",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "<?php echo e($company && $company->address ? $company->address : '', false); ?>",
            "addressLocality": "<?php echo e($company && $company->city ? $company->city : '', false); ?>",
            "addressRegion": "<?php echo e($company && $company->state ? $company->state : '', false); ?>",
            "postalCode": "<?php echo e($company && $company->postal_code ? $company->postal_code : '', false); ?>",
            "addressCountry": "<?php echo e($company && $company->country ? $company->country : 'US', false); ?>"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "<?php echo e($company && $company->phone ? $company->phone : '+1-555-123-4567', false); ?>",
            "contactType": "customer service",
            "email": "<?php echo e($company && $company->email ? $company->email : 'info@newlinetech.com', false); ?>",
            "availableLanguage": ["en"]
        },
        "sameAs": [
            <?php
                $socialUrls = array_filter([
                    $company && $company->facebook_url ? $company->facebook_url : null,
                    $company && $company->twitter_url ? $company->twitter_url : null,
                    $company && $company->linkedin_url ? $company->linkedin_url : null,
                    $company && $company->instagram_url ? $company->instagram_url : null,
                    url('/knowledge-base')
                ]);
            ?>
            <?php $__currentLoopData = $socialUrls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                "<?php echo e($url, false); ?>"<?php if($index < count($socialUrls) - 1): ?>,<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        ],
        "knowsAbout": [
            "School Management System",
            "Education Technology",
            "Student Information System",
            "School Administration",
            "Academic Management",
            "Educational Software"
        ],
        "areaServed": {
            "@type": "Place",
            "name": "Worldwide"
        },
        "serviceType": "Education Technology Services",
        "founder": {
            "@type": "Organization",
            "name": "<?php echo e($company->name ?? Utils::company_name(), false); ?>"
        }
    }
    </script>
    
    <?php echo $__env->yieldPushContent('structured-data'); ?>

    <!-- Google Analytics 4 (GA4) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-484716763"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-484716763', {
            page_title: '<?php echo e($company->app_name ?? Utils::app_name(), false); ?>',
            custom_map: {
                'dimension1': 'school_name',
                'dimension2': 'user_type'
            }
        });

        // Enhanced ecommerce and custom events
        gtag('event', 'page_view', {
            page_title: document.title,
            page_location: window.location.href,
            school_name: '<?php echo e($company->name ?? Utils::company_name(), false); ?>',
            app_version: '<?php echo e(config("app.version", "1.0"), false); ?>'
        });
    </script>

    <!-- Google tag (gtag.js) - Google Ads Conversion Tracking -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-778308285"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'AW-778308285');
    </script>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="<?php echo e(url('/'), false); ?>" class="logo">
                    <img src="<?php echo e($company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo(), false); ?>" 
                         alt="<?php echo e($company->name ?? Utils::company_name(), false); ?>" 
                         width="40" 
                         height="40"
                         loading="eager">
                    <?php echo e($company->name ?? Utils::company_name(), false); ?>

                </a>

                <div class="header-actions">
                    <a href="<?php echo e(url('access-system'), false); ?>" class="btn btn-primary">
                        Access the System
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4><?php echo e($company->name ?? Utils::company_name(), false); ?></h4>
                    <p>Transforming education through innovative school management solutions.</p>
                </div>

                <div class="footer-section">
                    <h4>Contact</h4>
                    <a href="mailto:<?php echo e($company && $company->email ? $company->email : 'info@newlinetech.com', false); ?>"><?php echo e($company && $company->email ? $company->email : 'info@newlinetech.com', false); ?></a>
                    <a href="tel:<?php echo e($company && $company->phone ? str_replace([' ', '(', ')', '-'], '', $company->phone) : '+15551234567', false); ?>"><?php echo e($company && $company->phone ? $company->phone : '+1 (555) 123-4567', false); ?></a>
                </div>

                <div class="footer-section">
                    <h4>System Access</h4>
                    <a href="<?php echo e(url('access-system'), false); ?>">Access the System</a>
                    <a href="<?php echo e(url('/admin/auth/login'), false); ?>">Admin Portal</a>
                    <a href="<?php echo e(route('knowledge-base.index'), false); ?>">Knowledge Base</a>
                    <a href="<?php echo e(url('/auth/support'), false); ?>">Support Center</a>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo e(date('Y'), false); ?> <?php echo e($company->name ?? Utils::company_name(), false); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH /Applications/MAMP/htdocs/mru/resources/views/layouts/modern-public.blade.php ENDPATH**/ ?>