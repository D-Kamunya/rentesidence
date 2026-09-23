{{--
    Typography for a Knowledge Base article body (raw HTML: h2–h4, p, ul/ol, strong,
    a, code, blockquote). Add class="kb-prose" to the body wrapper and @include this
    once on the page. Mirrors the finance-partner .cs-kb-body treatment so every
    account's KB reads with the same polish. Self-contained hex values (no design
    tokens) so it works on any panel.
--}}
<style>
    .kb-prose { font-size: 15px; line-height: 1.8; color: #374151; }
    .kb-prose > :first-child { margin-top: 0; }
    .kb-prose h2 { font-size: 20px; font-weight: 700; color: #0F3C7A; margin: 28px 0 10px; letter-spacing: -.01em; }
    .kb-prose h3 { font-size: 16.5px; font-weight: 600; color: #0F3C7A; margin: 24px 0 8px; letter-spacing: -.01em; }
    .kb-prose h4 { font-size: 14.5px; font-weight: 600; color: #185FA5; margin: 20px 0 6px; }
    .kb-prose p { margin: 0 0 14px; }
    .kb-prose ul, .kb-prose ol { margin: 0 0 15px; padding-left: 22px; }
    .kb-prose ul { list-style: disc; }
    .kb-prose ol { list-style: decimal; }
    .kb-prose li { margin-bottom: 7px; padding-left: 3px; }
    .kb-prose li::marker { color: #185FA5; }
    .kb-prose li > ul, .kb-prose li > ol { margin: 7px 0 0; }
    .kb-prose strong, .kb-prose b { color: #111827; font-weight: 600; }
    .kb-prose em { font-style: italic; }
    .kb-prose a { color: #185FA5; text-decoration: underline; text-underline-offset: 2px; }
    .kb-prose a:hover { color: #0F3C7A; }
    .kb-prose code { background: #F3F4F6; border: 1px solid #e5e7eb; border-radius: 5px; padding: .08em .4em; font-size: .88em; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; color: #1f2937; }
    .kb-prose blockquote { border-left: 3px solid #B5D4F4; background: #F7FAFD; margin: 0 0 15px; padding: 10px 16px; color: #4b5563; border-radius: 0 8px 8px 0; }
    .kb-prose hr { border: 0; border-top: 1px solid #eef2f7; margin: 24px 0; }
    .kb-prose img { max-width: 100%; height: auto; border-radius: 10px; }
    .kb-prose h2:first-child, .kb-prose h3:first-child, .kb-prose h4:first-child { margin-top: 0; }
</style>
