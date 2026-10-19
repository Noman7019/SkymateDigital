<?php
// Server-rendered summaries (crawlable without JS) + structured data for the main static pages.
// Text is taken from the existing site copy. Included by render.php.
declare(strict_types=1);
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'pages.php') { http_response_code(404); exit; }

// Social profiles the site itself links to (footer).
const SMD_SAME_AS = [
    'https://www.instagram.com/skymatedigital/',
    'https://www.facebook.com/profile.php?id=61562659003504',
    'https://www.linkedin.com/company/skymatedigital/',
];

function smd_static_nav(): string {
    $links = ['/' => 'Home', '/about' => 'About', '/services' => 'Services', '/services/store-development' => 'Shopify Store Development',
        '/services/add-branding' => 'Branding', '/services/ai-automation' => 'AI Automation', '/portfolio' => 'Portfolio',
        '/content-creation' => 'Content Creation', '/blog' => 'Blog', '/contact-us' => 'Contact'];
    $o = '<nav aria-label="Site"><ul>';
    foreach ($links as $u => $t) $o .= '<li><a href="' . $u . '">' . h($t) . '</a></li>';
    return $o . '</ul></nav>';
}

/** @return array{h1:string, html:string, crumb:?string, service:?array} */
function smd_static_page(string $path): ?array {
    $P = fn(string ...$ps) => implode('', array_map(fn($p) => '<p>' . h($p) . '</p>', $ps));
    $L = fn(array $items) => '<ul>' . implode('', array_map(fn($i) => '<li>' . h($i) . '</li>', $items)) . '</ul>';
    $cta = '<p><a href="/contact-us">Get a free store audit</a> · <a href="/services">Explore our services</a></p>';
    switch ($path) {
    case '/': return ['h1' => "Your Shopify Store Is Leaking Sales. We'll Find the Leaks Free and Fixed in 21 Days.", 'crumb' => null, 'service' => null,
        'html' => $P('Get a free conversion audit from Shopify experts. Zero risk, zero fluff - just a clear roadmap to more revenue.',
            'Most Shopify brands doing $10K–$100K/month are leaking sales through weak offers, slow pages, and creative that stopped converting. Our free audit shows you the exact leaks. Our team plugs them.')
            . '<h2>What we do</h2>' . $L(['Shopify store development: high-converting stores built for speed, scalability and conversions',
                'Brand identity & branding: logos, packaging and creative systems that make a brand impossible to scroll past',
                'AI automation: AI-driven customer service and workflow automation for e-commerce'])
            . '<h2>A process built for clarity and speed.</h2>' . $L(['Free consultation', 'Discover the product', 'Wireframe & production', 'Prototype & launch']) . $cta];
    case '/about': return ['h1' => 'Brand it, sell it, scale it', 'crumb' => 'About', 'service' => null,
        'html' => $P('A growth partner for ecommerce brands worldwide — Shopify & Amazon builds, performance media buying, and branding that converts.')
            . '<h2>From Startup to Strategic Partner</h2>' . $P('Founded with a passion for ecommerce, Sky mate digital has a history of delivering excellence — evolving, adapting, and aiming to exceed expectations. Ensuring customer satisfaction is our foremost priority.',
                'Today we help global brands launch and scale on Shopify and Amazon, run performance media across Meta, Google and TikTok, and build brand identities that convert — one accountable team, end to end.')
            . '<h2>Vision</h2>' . $P("Elevate your brand's digital presence with Sky mate digital's ecommerce expertise — we aspire to lead in performance-driven growth and help brands sell with confidence.")
            . '<h2>Mission</h2>' . $P("Unleash your brand's potential — empowering ecommerce businesses through high-converting stores, smart media buying, and branding built for growth.")
            . '<h2>What Drives Us</h2>' . $L(['Innovation', 'Integrity', 'Collaboration', 'Excellence'])];
    case '/services': return ['h1' => 'Where strategy meets digital', 'crumb' => 'Services', 'service' => null,
        'html' => $P('Everything you need to grow your online store, delivered with clarity and care.')
            . '<h2>Our services</h2><ul><li><a href="/services/store-development">Shopify Store Development</a> — building high-converting Shopify stores designed to grow your business and maximise sales.</li>'
            . '<li><a href="/services/add-branding">Brand Identity &amp; Branding</a> — building memorable brands that inspire trust, recognition, and long-term business growth.</li>'
            . '<li><a href="/services/ai-automation">AI Automation</a> — AI-driven customer service and business automation systems for high-volume e-commerce.</li></ul>' . $cta];
    case '/services/store-development': return ['h1' => 'Shopify Store Development', 'crumb' => 'Shopify Store Development',
        'service' => ['Shopify Store Development', 'Building high-converting Shopify stores designed to grow your business and maximise sales.'],
        'html' => $P('Building high-converting Shopify stores designed to grow your business and maximise sales.',
            "A successful Shopify store is more than just an attractive website—it's a complete eCommerce solution built for performance, conversions, and scalability. We create premium Shopify stores with custom designs, advanced integrations, marketing tools, and automation that help your business sell more while reducing manual work.")
            . '<h2>Building measurable outcomes</h2>' . $P('Every Shopify store is equipped with essential analytics and marketing tools including Google Analytics 4 (GA4), Microsoft Clarity, Google Tag Manager, Meta Pixel, and Google Search Console. We also implement technical SEO, optimise Core Web Vitals, integrate automation workflows, and ensure your store is fast, secure, scalable, and ready for long-term business growth.')
            . '<h2>What you get</h2>' . $L(['Custom Shopify Store Development', 'Premium Theme Customisation', 'Conversion-Focused UI/UX Design', 'Mobile-First Responsive Design', 'GA4 & Analytics Integration', 'Technical SEO Optimisation', 'App Integration & Automation', 'Performance & Security Optimisation']) . $cta];
    case '/services/add-branding': return ['h1' => 'Brand Identity & Branding', 'crumb' => 'Branding',
        'service' => ['Brand Identity & Branding', 'Building memorable brands that inspire trust, recognition, and long-term business growth.'],
        'html' => $P('Building memorable brands that inspire trust, recognition, and long-term business growth.',
            "Your brand is more than a logo—it's how customers perceive, remember, and connect with your business. We create complete brand identities that communicate your values, differentiate you from competitors, and provide a consistent experience across every customer touchpoint.")
            . '<h2>What you get</h2>' . $L(['Custom Logo Design', 'Brand Style Guide', 'Packaging Design', 'Social Media Branding', 'Marketing Collateral'])
            . '<h2>Our Branding Process</h2>' . $L(['Brand Discovery: understand your business, customers, competitors, and market.', 'Strategy & Identity: positioning, messaging, logo, colour palette, typography, and visual identity system.', 'Brand Launch: complete brand assets, guidelines, and marketing materials ready for print, digital platforms, and eCommerce.']) . $cta];
    case '/services/ai-automation': return ['h1' => 'Customer Support Automation for NoblTravel', 'crumb' => 'AI Automation',
        'service' => ['AI Automation for E-commerce', 'AI-driven customer service and end-to-end business automation systems for high-volume e-commerce.'],
        'html' => $P('At Sky Mate Digital, we specialize in building automation systems that transform how businesses operate. One of our notable implementations was for NoblTravel, a high-volume e-commerce store handling thousands of customer interactions every day.')
            . '<h2>The Challenge</h2>' . $P('NoblTravel receives thousands of customer inquiries related to orders, returns, product questions, and post-purchase support. Managing this volume manually created operational pressure, higher costs, and slower response times.')
            . '<h2>Our Solution</h2>' . $P('Our team designed and deployed a fully automated AI-driven customer service infrastructure that could handle large volumes of support requests in real time.') . $cta];
    case '/portfolio': return ['h1' => 'Featured projects', 'crumb' => 'Portfolio', 'service' => null,
        'html' => $P('Unleashing the power of innovation and creativity—web builds, SEO wins, integrations, and campaigns shipped with clients across the globe.')
            . '<h2>Innovation and creativity in action</h2>' . $L(['Kings Institute', 'Makaan', 'ZB Creators', 'Sell It Easy', 'IELTS PrepHub', 'Pak British Education'])];
    case '/contact-us': return ['h1' => 'Contact us', 'crumb' => 'Contact', 'service' => null,
        'html' => $P("Premium support with personalized solutions—tell us what you're building, and we'll respond with clear next steps.")
            . '<h2>Send us a message</h2>' . $P("Share a few details and we'll route your request to the right team. On business days, we typically reply within 24 hours. We can sign NDAs before sharing sensitive details.")];
    case '/content-creation': return ['h1' => 'Content Creation', 'crumb' => 'Content Creation', 'service' => null,
        'html' => '<h2>Brand Content</h2>' . $P('Short-form video and brand content produced by Sky Mate Digital for e-commerce brands.')];
    case '/privacy-policy': return ['h1' => 'Privacy Policy', 'crumb' => 'Privacy Policy', 'service' => null,
        'html' => $P('How Sky Mate Digital collects, uses, and safeguards your personal information when you engage our Shopify store creation, branding, advertising, and growth services.', 'We do not sell your personal data.')];
    case '/terms-of-service': case '/terms-condition': return ['h1' => 'Terms of Service', 'crumb' => 'Terms of Service', 'service' => null,
        'html' => $P('These Terms govern your access to our website and the e-commerce Services we provide, including Shopify store creation, branding, paid advertising, and store management. By using our Site or engaging our Services, you accept these Terms.')];
    }
    return null;
}

/** Extra Organization/ProfessionalService properties (merged into render.php's $org). */
function smd_org_extra(string $site): array {
    return ['@type' => ['Organization', 'ProfessionalService'], 'logo' => ['@type' => 'ImageObject', 'url' => $site . '/logo-512.png', 'width' => 512, 'height' => 512],
        'image' => $site . '/og-image.png', 'sameAs' => SMD_SAME_AS,
        'founder' => ['@type' => 'Person', 'name' => 'Noman Rashid', 'jobTitle' => 'CEO']];
}
function smd_website_ld(string $site): array {
    return ['@context' => 'https://schema.org', '@type' => 'WebSite', '@id' => $site . '/#website', 'url' => $site . '/', 'name' => SMD_SITE_NAME,
        'inLanguage' => 'en', 'publisher' => ['@id' => $site . '/#organization']];
}
