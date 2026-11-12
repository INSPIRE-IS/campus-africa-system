@extends('layouts.app')

@section('content')
<style>
    .privacy-page {
        max-width: 960px;
        margin: 0 auto;
        padding: 38px 20px 64px;
        color: #292929;
        line-height: 1.65;
    }

    .privacy-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 34px;
        border-bottom: 1px solid #d2d2d2;
    }

    .privacy-tab {
        display: inline-block;
        padding: 12px 18px;
        border: 1px solid transparent;
        border-bottom: 3px solid transparent;
        color: #333;
        font-weight: 700;
        text-decoration: none;
    }

    .privacy-tab:hover,
    .privacy-tab:focus {
        color: #bd3c20;
    }

    .privacy-tab.is-active {
        border-bottom-color: #e74f26;
        color: #bd3c20;
    }

    .privacy-document h2 {
        margin: 0 0 6px;
        font-size: 30px;
    }

    .privacy-document h3 {
        margin: 34px 0 10px;
        font-size: 20px;
    }

    .privacy-document p,
    .privacy-document ul {
        margin: 0 0 16px;
    }

    .privacy-document li {
        margin-bottom: 8px;
    }

    .privacy-updated {
        margin-bottom: 28px !important;
        color: #666;
    }

    .privacy-notice {
        padding: 14px 16px;
        border-left: 4px solid #e1b512;
        background: #f7f7f7;
    }

    @media (max-width: 767px) {
        .privacy-tabs {
            flex-direction: column;
            border-bottom: 0;
        }

        .privacy-tab {
            border: 1px solid #d2d2d2;
            border-left: 4px solid transparent;
        }

        .privacy-tab.is-active {
            border-left-color: #e74f26;
            border-bottom-color: #d2d2d2;
        }

        .privacy-document h2 {
            font-size: 25px;
        }
    }
</style>

<div class="yellowGradient" style="height:360px !important">
    <div class="container">
        <nav class="navBar">
            <div class="leftMenu">
                <a href="https://campusafrica.co.za">
                    <img src="/images/logo/Campus_Africa_Logo.png" alt="Campus Africa Logo" width="92" height="91">
                </a>
            </div>
            <div class="rightMenu">
                <a href="https://campusafrica.co.za/contact-us/" class="contactButton">contact us</a>
            </div>
        </nav>

        <div class="loginHeader">
            <br/>
            <h1 class="booking">Privacy Policy</h1>
        </div>
    </div>
</div>

<main class="privacy-page">
    <nav class="privacy-tabs" aria-label="Privacy policies">
        <a href="{{ route('privacy-policy', ['policy' => 'doomscroller']) }}"
            class="privacy-tab {{ $policy === 'doomscroller' ? 'is-active' : '' }}"
            aria-current="{{ $policy === 'doomscroller' ? 'page' : 'false' }}">
            Doomscroller App
        </a>
        <a href="{{ route('privacy-policy', ['policy' => 'campus-africa']) }}"
            class="privacy-tab {{ $policy === 'campus-africa' ? 'is-active' : '' }}"
            aria-current="{{ $policy === 'campus-africa' ? 'page' : 'false' }}">
            Campus Africa Website
        </a>
    </nav>

    @if ($policy === 'campus-africa')
        <article class="privacy-document">
            <h2>Campus Africa Website Privacy Notice</h2>
            <p class="privacy-notice">
                This notice relates to the Campus Africa accommodation website and application process. It is separate from the Doomscroller Android app privacy policy.
            </p>

            <h3>Customer privacy</h3>
            <p>
                Campus Africa shall take all reasonable steps to protect the personal information of users. For the purpose of this clause, "personal information" shall be defined as detailed in the Promotion of Access to Information Act 2 of 2000 (PAIA).
            </p>
            <p>
                The PAIA may be downloaded from:
                <a href="http://www.lawsofsouthafrica.up.ac.za/index.php/current-legislation" target="_blank" rel="noopener">lawsofsouthafrica.up.ac.za</a>.
            </p>
            <p>
                This is the privacy wording currently published in the Campus Africa
                <a href="{{ url('/terms') }}">Terms and Conditions</a>.
            </p>
        </article>
    @else
        <article class="privacy-document">
            <h2>Doomscroller - Privacy Policy</h2>
            <p class="privacy-updated">Last updated: November 2026</p>

            <p>Doomscroller is an Android app published on Google Play by Consolidated Urban Management Corporation (Pty) Ltd, shown on Google Play as Campus Africa Student Accommodation ("we"). It lets you set daily time limits for social apps you choose, and covers those apps with a rest screen when your time is up. This policy explains what the app accesses on your phone and what happens to that information.</p>

            <h3>The short version</h3>
            <p>Doomscroller does not collect, send or share your personal data. It has no account, no advertising, no analytics and no internet access. Everything it keeps stays on your phone, and we cannot see it. Subscription payments are handled by Google Play.</p>

            <h3>What the app looks at, and why</h3>
            <ul>
                <li>Which app is on screen. Doomscroller needs this to time the apps you picked and cover them when their time is up.</li>
                <li>Inside YouTube only, the names of YouTube's screen parts. This is how it tells a YouTube Short from a normal video.</li>
                <li>Whether the apps it can limit are installed, so it can show you which ones apply.</li>
            </ul>
            <p>Doomscroller does not read your messages, what you type, or the words and pictures on your screen. It does not store or share what it sees. It does not tap or change anything in other apps, except to go to your home screen when you press a button on its rest screen.</p>

            <h3>What is kept on your phone</h3>
            <ul>
                <li>Your settings: the apps you picked, your limits, and your rest and comeback choices.</li>
                <li>Today's activity: the minutes you have used in each picked app, and any rests and comebacks.</li>
                <li>A running count of YouTube time since a Short was last spotted, so the app can warn you if it has stopped recognising Shorts.</li>
            </ul>
            <p>This information is held in the app's private storage on your device. It is never sent to us or to anyone else.</p>

            <h3>Permissions the app uses</h3>
            <ul>
                <li><strong>Accessibility service:</strong> to notice which app is on screen, as described above. You are asked to agree before it is used, and you can switch it off at any time in your phone's settings.</li>
                <li><strong>Usage access:</strong> a backup way to see which app is open.</li>
                <li><strong>Display over other apps:</strong> to show the rest screen.</li>
                <li><strong>Notifications and running in the foreground:</strong> to show a small permanent notification while the app is on, and to warn you if something stops it working.</li>
                <li><strong>Start at boot, and Battery: Unrestricted if you turn it on:</strong> so the app keeps working after a restart and is not put to sleep in the background.</li>
            </ul>

            <h3>Payments</h3>
            <p>Doomscroller is a yearly subscription with a free trial. Subscriptions are bought, renewed and cancelled through Google Play, under Google's terms. We never see your card or payment details. On your phone, the app asks Google Play whether you have an active subscription. Google gives us limited records of each purchase, such as the order number, date, amount and country. We use these only for accounts, tax and support, and keep them for as long as the law requires.</p>

            <h3>Sharing</h3>
            <p>We do not sell or share any information from the app, because it never leaves your phone.</p>

            <h3>Keeping and deleting your information</h3>
            <p>Daily counts reset at midnight. Your settings stay on your phone until you clear the app's data or uninstall it, either of which removes everything the app has stored. We hold no copy of it, so there is nothing further for us to delete. Purchase records are covered under Payments above.</p>
            <p>If you email us, we will use your email address and message only to reply to you, and will keep them only for as long as we need to deal with your enquiry. You can ask us to delete them at any time.</p>

            <h3>Google Play</h3>
            <p>Downloading the app through Google Play is covered by Google's own terms and privacy policy.</p>

            <h3>Children</h3>
            <p>Doomscroller is intended for adults managing their own time. It is not directed at children.</p>

            <h3>Changes to this policy</h3>
            <p>If this policy changes, we will update this page and the date at the top.</p>

            <h3>Contact</h3>
            <p>Questions about this policy or the app: <a href="mailto:doomscroller.support@gmail.com">doomscroller.support@gmail.com</a></p>
        </article>
    @endif
</main>
@endsection
