<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630" role="img" aria-labelledby="title description">
    <title id="title">{{ $title }}</title>
    <desc id="description">{{ $description }}</desc>
    <defs>
        <linearGradient id="background" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#fff8ed" />
            <stop offset="1" stop-color="#ffe2c2" />
        </linearGradient>
        <linearGradient id="accent" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#f97316" />
            <stop offset="1" stop-color="#ea580c" />
        </linearGradient>
    </defs>
    <rect width="1200" height="630" fill="url(#background)" />
    <circle cx="1080" cy="100" r="220" fill="#fb923c" opacity=".12" />
    <circle cx="80" cy="610" r="250" fill="#fdba74" opacity=".2" />
    <rect x="64" y="62" width="1072" height="506" rx="32" fill="#fff" opacity=".82" />
    <rect x="104" y="112" width="12" height="406" rx="6" fill="url(#accent)" />
    <text x="154" y="190" fill="#c2410c" font-family="sans-serif" font-size="25" font-weight="700" letter-spacing="3">{{ $eyebrow }}</text>
    <text x="154" y="320" fill="#292524" font-family="sans-serif" font-size="{{ mb_strlen($title) > 20 ? 42 : 64 }}" font-weight="700">{{ mb_strlen($title) > 24 ? mb_substr($title, 0, 24).'…' : $title }}</text>
    <text x="154" y="390" fill="#57534e" font-family="sans-serif" font-size="30">{{ mb_strlen($description) > 44 ? mb_substr($description, 0, 44).'…' : $description }}</text>
    <rect x="154" y="448" width="250" height="58" rx="29" fill="url(#accent)" />
    <text x="279" y="486" fill="#fff" font-family="sans-serif" font-size="23" font-weight="700" text-anchor="middle">メニューガチャ</text>
    <text x="1090" y="510" fill="#a8a29e" font-family="sans-serif" font-size="18" text-anchor="end">MENU GACHA</text>
</svg>
