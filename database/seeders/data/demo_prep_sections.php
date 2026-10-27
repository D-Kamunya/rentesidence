<?php

// Default DEMO-PREP guide sections (admin → Marketing → Demo Prep; guides affiliates
// through running a demo). Reviewed set. Edit here; DemoPrepSeeder upserts (self-healing,
// authoritative-once). The demo-account creds (demo_settings) are a per-env MANUAL go-live
// step, NOT seeded here.

return array (
  0 => 
  array (
    'title' => 'Pre-Demo Checklist',
    'content' => '- Log into the demo account before the call and confirm everything loads correctly
- Have the lead\'s details open — company name, property type, estimated units
- Set up your screen share (Zoom or Google Meet) and test audio
- Close unnecessary browser tabs so the demo account is front and centre
- Have this guide open on a second screen or your phone for reference',
    'sort_order' => 1,
  ),
  1 => 
  array (
    'title' => 'Recommended Walkthrough Order',
    'content' => '1. Dashboard — start here, show the big picture first so they see the end result
2. Properties & Units — most tangible section, they immediately relate to it
3. Tenant Management — show how tenants are added, rent tracked, and communication logged
4. Rent Collection & Invoicing — this is usually the biggest pain point, spend time here
5. Maintenance Requests — good differentiator, shows operational value
6. Reports — close with this, tie everything back to visibility and time saved',
    'sort_order' => 2,
  ),
  2 => 
  array (
    'title' => 'Key Talking Points',
    'content' => 'Dashboard:
"Everything your team needs is visible from one screen — no more jumping between spreadsheets."

Properties & Units:
"You can manage all your properties and units from one place, whether you have 10 or 1,000."

Rent Collection:
"The system automatically tracks who has paid, who hasn\'t, and sends reminders — so you don\'t have to chase manually."

Maintenance:
"Tenants log requests directly, your team gets notified, and everything is tracked to resolution."

Reports:
"At any point you can pull a full financial or occupancy report — no more end-of-month scramble."',
    'sort_order' => 3,
  ),
  3 => 
  array (
    'title' => 'Common Objections & Responses',
    'content' => '"We already use spreadsheets and it works fine."
→ "Spreadsheets work until they don\'t — the challenge is usually when the portfolio grows or staff changes. This gives you the same control but with automation and a proper audit trail."

"It looks complicated."
→ "Most clients are up and running within a day. You can start free and explore it at your own pace — and I\'m available to walk you through anything."

"We\'re not ready to switch right now."
→ "That\'s completely fine. You can start on the free plan with no commitment — just a chance to see whether it fits. What would need to change for the timing to be right?"

"How much does it cost?"
→ "You can start completely free and only pay for extras as you grow, so it scales with your portfolio. Let\'s make sure it\'s the right fit first — we can look at a paid plan together once you\'re ready."',
    'sort_order' => 4,
  ),
  4 => 
  array (
    'title' => 'How to Close the Demo',
    'content' => 'Don\'t let the demo end without a clear next step. Once you\'ve finished the walkthrough, ask:

"Based on what you\'ve seen today, does this look like it could work for your business?"

If yes → "The easiest next step is getting you set up on Free so you can start with your own data — it\'s free, nothing to pay. I\'ll send you the activation link right now and you set your own password to go live. (If you\'d rather try the paid features first, I can arrange a short trial of those too — just say the word.)"

If hesitant → "What would you need to see to feel confident? I\'m happy to go deeper on any part of the system."

If not ready → "No pressure at all — what\'s the main thing holding you back? Even knowing that helps me make sure we\'re showing you the right things."

Always end with a specific follow-up commitment — a date, a call, something concrete. A demo with no next step is a demo that goes cold.',
    'sort_order' => 5,
  ),
);
