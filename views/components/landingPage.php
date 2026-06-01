<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Finzo – Your money. Finally under control.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="/pocket_money/public/css/style.css">
    <link rel="stylesheet" href="/pocket_money/public/css/landingPage.css">
    <link rel="stylesheet" href="/pocket_money/public/css/nav.css">
</head>

<body>

    <!-- NAV -->
    <nav>
        <div class="container nav-inner">
            <div class="nav-logo">Finzo</div>
            <div class="nav-links d-none d-md-flex">
                <a href="#">Features</a>
                <a href="#">How it works</a>
                <a href="#">Pricing</a>
            </div>
            <div class="nav-actions">
                <a class="btn-login d-none d-md-block" href="/pocket_money/views/login.php">Log in</a>
                <a class="btn-cta" href="/pocket_money/views/login.php">Get started free</a>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero">
        <div class="container">
            <div class="hero-badge"><span></span> New with group budgets!</div>
            <h1>Your money.<br>Finally <span class="accent">under control.</span></h1>
            <p class="hero-sub">Track every dirham, split expenses with your team, and never miss a budget limit again.
            </p>
            <div class="hero-btns">
                <button class="btn-hero-primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                    Start free — no card needed
                </button>
                <button class="btn-hero-secondary">Watch demo</button>
            </div>
            <div class="hero-social-proof">
                <div class="avatars">
                    <span class="av1">JK</span>
                    <span class="av2">SA</span>
                    <span class="av3">MR</span>
                    <span class="av4">TN</span>
                </div>
                <span>12,000+ users already saving smarter</span>
            </div>

            <!-- Dashboard Preview -->
            <div class="preview-wrap">
                <div class="preview-label">What you get</div>
                <div class="preview-grid">
                    <!-- Stats -->
                    <div style="display:flex; flex-direction:column; gap:14px;">
                        <div class="dash-card">
                            <div class="label">Net Balance</div>
                            <div class="value">4200 TND</div>
                        </div>
                        <div class="dash-card">
                            <div class="label">Income</div>
                            <div class="value positive">+6500 TND</div>
                        </div>
                        <div class="dash-card">
                            <div class="label">Expenses</div>
                            <div class="value negative">-2200 TND</div>
                        </div>
                    </div>
                    <!-- Chart -->
                    <div class="dash-card chart-card">
                        <div class="label">Spending by category</div>
                        <div class="bars">
                            <div class="bar bar-teal" style="height:72%"></div>
                            <div class="bar bar-yellow" style="height:55%"></div>
                            <div class="bar bar-purple" style="height:88%"></div>
                            <div class="bar bar-blue" style="height:40%"></div>
                            <div class="bar bar-orange" style="height:62%"></div>
                            <div class="bar bar-teal" style="height:50%"></div>
                            <div class="bar bar-yellow" style="height:78%"></div>
                        </div>
                        <div class="chart-legend">
                            <div class="legend-item">
                                <div class="legend-dot" style="background:var(--teal)"></div>Food
                            </div>
                            <div class="legend-item">
                                <div class="legend-dot" style="background:var(--yellow)"></div>Transport
                            </div>
                            <div class="legend-item">
                                <div class="legend-dot" style="background:var(--purple)"></div>Housing
                            </div>
                        </div>
                        <div class="progress-list" style="margin-top:16px;">
                            <div class="prog-row">
                                <div class="prog-label"><span>Food</span><span>680 TND</span></div>
                                <div class="prog-bar-bg">
                                    <div class="prog-bar-fill" style="width:68%; background:var(--teal)"></div>
                                </div>
                            </div>
                            <div class="prog-row">
                                <div class="prog-label"><span>Transport</span><span>320 TND</span></div>
                                <div class="prog-bar-bg">
                                    <div class="prog-bar-fill" style="width:42%; background:var(--yellow)"></div>
                                </div>
                            </div>
                            <div class="prog-row">
                                <div class="prog-label"><span>Housing</span><span>1200 TND</span></div>
                                <div class="prog-bar-bg">
                                    <div class="prog-bar-fill" style="width:85%; background:var(--purple)"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES -->
    <section class="features-bg">
        <div class="container">
            <div class="text-center">
                <div class="section-tag">Built for real financial clarity</div>
            </div>
            <div class="feat-grid">
                <div class="feat-card">
                    <div class="feat-icon icon-blue">📊</div>
                    <h4>Live dashboard</h4>
                    <p>Real-time charts showing your income, expenses, and saving balance — updated the moment you log a
                        transaction.</p>
                </div>
                <div class="feat-card">
                    <div class="feat-icon icon-teal">🎯</div>
                    <h4>Budget limits</h4>
                    <p>Set spending caps per category and get warned before you overshoot — no more end-of-month
                        surprises.</p>
                </div>
                <div class="feat-card">
                    <div class="feat-icon icon-yellow">🔔</div>
                    <h4>Smart alerts</h4>
                    <p>Instant push notifications when you're nearing your monthly limit — so you can course-correct in
                        real time.</p>
                </div>
                <div class="feat-card">
                    <div class="feat-icon icon-orange">👥</div>
                    <h4>Group budgets</h4>
                    <p>Invite teammates or family to track shared expenses together — perfect for households and small
                        teams.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- STEPS -->
    <section class="steps-bg">
        <div class="container">
            <div class="section-tag">Simple by design</div>
            <div class="section-title">3 steps to financial clarity</div>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-num num-1">1</div>
                    <h4>Sign up free</h4>
                    <p>Create your account in 30 seconds. No credit card, no commitment. You're in instantly.</p>
                </div>
                <div class="step-card">
                    <div class="step-num num-2">2</div>
                    <h4>Log transactions</h4>
                    <p>Add income and expenses manually or import from a CSV file — your full picture in minutes.</p>
                </div>
                <div class="step-card">
                    <div class="step-num num-3">3</div>
                    <h4>Hit your goals</h4>
                    <p>Watch your budget fill up and get alerts before you overspend — stay on track every month.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS -->
    <section class="testi-bg">
        <div class="container">
            <div class="testi-title">Loved by users</div>
            <div class="testi-grid">
                <div class="testi-card">
                    <div class="stars">★★★★★</div>
                    <blockquote>"Finally a budget app that makes sense. The group budget feature saved our team so much
                        time."</blockquote>
                    <div class="testi-author">
                        <div class="author-ava ava-a">SA</div>
                        <div>
                            <div class="author-name">Sara A.</div>
                            <div class="author-role">Product Manager</div>
                        </div>
                    </div>
                </div>
                <div class="testi-card">
                    <div class="stars">★★★★★</div>
                    <blockquote>"The alerts are a game-changer. I used to overspend every month — not anymore."
                    </blockquote>
                    <div class="testi-author">
                        <div class="author-ava ava-b">KM</div>
                        <div>
                            <div class="author-name">Karim M.</div>
                            <div class="author-role">Product Manager</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="container">
            <h2>Take control of your finances today</h2>
            <p>Join 12,000+ people managing their money smarter with BudgetFlow.</p>
            <a class="btn-cta-large" href="/pocket_money/views/login.php">Create your free account</a>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="container footer-inner">
            <div class="footer-copy">© 2026 BudgetFlow. All rights reserved.</div>
            <div class="footer-links">
                <a href="#">Privacy</a>
                <a href="#">Terms</a>
                <a href="#">Contact</a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>