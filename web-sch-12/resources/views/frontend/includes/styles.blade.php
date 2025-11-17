<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.lineicons.com/4.0/lineicons.css">
<style>
    :root {
        --primary: #4361ee;
        --secondary: #3f37c9;
        --accent: #4895ef;
        --dark: #1a1a2e;
        --light: #f8f9fa;
        --success: #4cc9f0;
        --gradient: linear-gradient(135deg, #4361ee 0%, #3f37c9 50%, #4895ef 100%);
    }

    body {
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        color: var(--dark);
        overflow-x: hidden;
        background-color: #fafbff;
    }

    /* Navbar */
    .navbar {
        padding: 1.5rem 0;
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
    }

    .navbar-brand {
        font-weight: 800;
        font-size: 1.8rem;
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .nav-link {
        font-weight: 500;
        margin: 0 0.5rem;
        color: var(--dark);
        transition: all 0.3s ease;
    }

    .nav-link:hover {
        color: var(--primary);
    }

    .btn-primary {
        background: var(--gradient);
        border: none;
        padding: 0.6rem 1.5rem;
        font-weight: 500;
        box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
    }

    /* Hero Section */
    .hero {
        padding: 6rem 0 5rem;
        background: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80') no-repeat center center;
        background-size: cover;
        position: relative;
    }

    .hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(26, 26, 46, 0.7);
    }

    .hero-content {
        position: relative;
        z-index: 2;
        color: white;
    }

    .hero h1 {
        font-weight: 800;
        font-size: 3.5rem;
        line-height: 1.2;
        margin-bottom: 1.5rem;
    }

    .hero p {
        font-size: 1.2rem;
        opacity: 0.9;
        margin-bottom: 2rem;
        max-width: 600px;
    }

    .hero-btns .btn {
        margin-right: 1rem;
        margin-bottom: 1rem;
    }

    .btn-outline-light {
        border: 2px solid white;
        background: transparent;
        color: white;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-outline-light:hover {
        background: white;
        color: var(--primary);
    }

    /* Features */
    .features {
        padding: 6rem 0;
        background: white;
    }

    .section-title {
        text-align: center;
        margin-bottom: 4rem;
    }

    .section-title h2 {
        font-weight: 800;
        font-size: 2.5rem;
        margin-bottom: 1rem;
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .section-title p {
        color: #6c757d;
        font-size: 1.1rem;
        max-width: 700px;
        margin: 0 auto;
    }

    .feature-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        height: 100%;
        border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(67, 97, 238, 0.1);
    }

    .feature-icon {
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(67, 97, 238, 0.1);
        border-radius: 12px;
        margin-bottom: 1.5rem;
        color: var(--primary);
        font-size: 1.8rem;
    }

    .feature-card h3 {
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .feature-card p {
        color: #6c757d;
    }

    /* Dashboard Preview */
    .dashboard-preview {
        padding: 6rem 0;
        background: linear-gradient(135deg, #f6f9ff 0%, #f0f4ff 100%);
    }

    .dashboard-img {
        border-radius: 12px;
        box-shadow: 0 25px 50px rgba(67, 97, 238, 0.15);
        overflow: hidden;
        transition: all 0.5s ease;
    }

    .dashboard-img:hover {
        transform: scale(1.02);
    }

    .dashboard-img img {
        width: 100%;
        height: auto;
        display: block;
    }

    /* Testimonials */
    .testimonials {
        padding: 6rem 0;
        background: white;
    }

    .testimonial-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
        border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .testimonial-header {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .testimonial-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 1rem;
    }

    .testimonial-author h5 {
        font-weight: 700;
        margin-bottom: 0.2rem;
    }

    .testimonial-author p {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 0;
    }

    .testimonial-quote {
        color: #6c757d;
        font-style: italic;
        position: relative;
    }

    .testimonial-quote::before {
        content: '"';
        font-size: 3rem;
        color: rgba(67, 97, 238, 0.1);
        position: absolute;
        top: -1.5rem;
        left: -1rem;
        font-family: serif;
    }

    /* Pricing */
    .pricing {
        padding: 6rem 0;
        background: linear-gradient(135deg, #f6f9ff 0%, #f0f4ff 100%);
    }

    .pricing-card {
        background: white;
        border-radius: 12px;
        padding: 2.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        margin-bottom: 2rem;
        border: 1px solid rgba(0, 0, 0, 0.03);
        position: relative;
        overflow: hidden;
    }

    .pricing-card.popular {
        border: 2px solid var(--primary);
    }

    .popular-badge {
        position: absolute;
        top: 0;
        right: 0;
        background: var(--gradient);
        color: white;
        padding: 0.3rem 1.5rem;
        font-size: 0.8rem;
        font-weight: 600;
        border-bottom-left-radius: 12px;
    }

    .pricing-card h3 {
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .price {
        font-size: 2.5rem;
        font-weight: 800;
        margin: 1.5rem 0;
        color: var(--dark);
    }

    .price span {
        font-size: 1rem;
        font-weight: 500;
        color: #6c757d;
    }

    .pricing-features {
        margin: 2rem 0;
    }

    .pricing-features li {
        margin-bottom: 0.8rem;
        display: flex;
        align-items: center;
    }

    .pricing-features li i {
        color: var(--primary);
        margin-right: 0.5rem;
    }

    /* CTA */
    .cta {
        padding: 6rem 0;
        background: var(--gradient);
        color: white;
        text-align: center;
    }

    .cta h2 {
        font-weight: 800;
        font-size: 2.5rem;
        margin-bottom: 1.5rem;
    }

    .cta p {
        font-size: 1.2rem;
        opacity: 0.9;
        margin-bottom: 2rem;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
    }

    /* Footer */
    .footer {
        padding: 4rem 0 2rem;
        background: var(--dark);
        color: white;
    }

    .footer-logo {
        font-weight: 800;
        font-size: 1.8rem;
        margin-bottom: 1.5rem;
        display: inline-block;
        color: white;
    }

    .footer p {
        opacity: 0.7;
        margin-bottom: 1.5rem;
    }

    .social-links a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        color: white;
        margin-right: 0.5rem;
        transition: all 0.3s ease;
    }

    .social-links a:hover {
        background: var(--primary);
        transform: translateY(-3px);
    }

    .footer-links h5 {
        font-weight: 700;
        margin-bottom: 1.5rem;
        font-size: 1.1rem;
    }

    .footer-links ul {
        list-style: none;
        padding: 0;
    }

    .footer-links li {
        margin-bottom: 0.8rem;
    }

    .footer-links a {
        color: rgba(255, 255, 255, 0.7);
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .footer-links a:hover {
        color: white;
        padding-left: 5px;
    }

    .copyright {
        padding-top: 2rem;
        margin-top: 2rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        text-align: center;
        opacity: 0.7;
        font-size: 0.9rem;
    }

    /* Animations */
    @keyframes float {
        0% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-15px);
        }

        100% {
            transform: translateY(0px);
        }
    }

    .floating {
        animation: float 6s ease-in-out infinite;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .hero h1 {
            font-size: 2.8rem;
        }
    }

    @media (max-width: 768px) {
        .hero {
            padding: 4rem 0;
            text-align: center;
        }

        .hero h1 {
            font-size: 2.2rem;
        }

        .hero p {
            margin-left: auto;
            margin-right: auto;
        }

        .hero-btns {
            justify-content: center;
        }

        .section-title h2 {
            font-size: 2rem;
        }
    }
</style>
