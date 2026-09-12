<?php

// Canonical DEFAULT marketing/action templates (affiliate suggestion engine).
// {{contact_name}} {{affiliate_name}} {{company_name}} {{company_country}} {{estimated_units}}
// {{property_type}} {{brochure_line}} are substituted at send. Edit here; MarketingTemplateSeeder upserts.

return array (
  0 => 
  array (
    'category' => 'demo_complete',
    'action_type' => 'whatsapp',
    'name' => 'WhatsApp Follow-Up — Trial Invitation',
    'message_template' => 'Hi {{contact_name}} 👋

Thank you for taking the time for the demo! I hope it gave you a clear picture of how the platform can work for {{company_name}}.

I wanted to check in. Did you have any questions after going through everything? I\'m happy to clarify anything or dig deeper into any specific area.

If you\'re ready to take the next step, I can arrange a sponsored trial account so your team can explore the system hands-on. Just say the word!

{{affiliate_name}}',
  ),
  1 => 
  array (
    'category' => 'demo_complete',
    'action_type' => 'call',
    'name' => 'Call Script — Post-Demo',
    'message_template' => 'CALL SCRIPT — POST-DEMO FOLLOW UP (HOT LEAD)

Opening:
"Hi {{contact_name}}, it\'s {{affiliate_name}}. I\'m following up from our demo. Do you have 5 minutes?"

Gauge interest:
"How did you find it overall? Was there anything that stood out as particularly useful for {{company_name}}?"

If positive:
"That\'s great to hear. The next step would be setting up a sponsored trial account so your team can actually get hands-on with the system. I can have that ready for you within 24 hours — shall we go ahead?"

Handle objections:
- "Need to think about it" → "Of course. What\'s the main thing you\'re weighing up? I want to make sure you have everything you need to make a comfortable decision."
- "Need to involve the team" → "That makes sense. Would it help if I joined a quick call with your team? I can answer any technical or commercial questions directly."
- "Not sure yet" → "Would a trial take some of the uncertainty away? There\'s no commitment — just a chance to see how it fits your workflow."

Close:
"What would be a good next step from your side?"',
  ),
  2 => 
  array (
    'category' => 'follow_up',
    'action_type' => 'call',
    'name' => 'Call Script — Follow-Up',
    'message_template' => 'CALL SCRIPT — FOLLOW-UP

Opening:
"Hi, is this {{contact_name}}? Great — this is {{affiliate_name}} calling about the property management system I mentioned / you enquired about."

If they remember you:
"Perfect. I wanted to call personally because based on what you told me about {{company_name}}, I think there\'s a really strong fit here. Can I take 5 minutes to explain why?"

Key talking points:
- Handles {{company_name}}\'s scale — designed for properties of all sizes
- Cuts admin time significantly — most clients see results in the first month
- Full support and onboarding included

Close:
"I\'d love to set up a proper demo so you can see everything first-hand. Does [day] or [day] work for a 20-minute call?"

If they hesitate:
"Completely understand — would it help if I sent over a quick overview first? I can follow up after you\'ve had a chance to look."',
  ),
  3 => 
  array (
    'category' => 'intro',
    'action_type' => 'whatsapp',
    'name' => 'WhatsApp Intro — Warm Lead',
    'message_template' => 'Hi {{contact_name}} 👋

This is {{affiliate_name}}. I\'m reaching out about a Real Estate property management ecosystem that a number of companies in your area have been using to simplify their operations.

It handles everything from rent collection, tenant communication to maintenance tracking, financial reporting and onlines sales for you to increase cashflow  — all in one place. You could even access growth, development or personal loans solely secured by your rental cashflow!

Would you be open to a quick demo this week? Takes about 20 minutes and I can show you exactly how it works for companies like yours.',
  ),
  4 => 
  array (
    'category' => 'intro',
    'action_type' => 'email',
    'name' => 'Email — Cold Lead Intro',
    'message_template' => 'Hi {{contact_name}},

I hope this message finds you well. My name is {{affiliate_name}}, and I\'m reaching out because I believe Centresidence Real Estate Technologies could greatly benefit {{company_name}}.

Our systems helps property managers and owners like yourself streamline operations, reduce administrative costs, improve tenant satisfaction and increase cash flow – all from a single dashboard. You could even access growth, development or personal loans solely secured by your rental income flow.

{{brochure_line}} I\'d love to schedule a quick 5-10 minute call to walk you through how it works and answer any questions.

Would you be open to a brief conversation this week?

Warm regards,
{{affiliate_name}}',
  ),
  5 => 
  array (
    'category' => 'intro',
    'action_type' => 'call',
    'name' => 'Call Script — Introduction',
    'message_template' => 'INTRODUCTION CALL SCRIPT
━━━━━━━━━━━━━━

OPENING
───────
"Hi, could I speak with {{contact_name}} please?"

[Once connected]

"Hi {{contact_name}}, my name is {{affiliate_name}}. I\'m reaching out because I work with Centresidence Real Estate Technologies, a property management ecosystem that I think could be a really good fit for {{company_name}}. Do you have about 2 minutes?"


IF THEY SAY YES
───────────────
"Great, thank you. So we work with property managers and owners across {{company_country}} — companies managing anywhere from a handful of units right up to large portfolios. 

Based on what I know about {{company_name}} — you\'re managing around {{estimated_units}} units. Our platform could really help streamline your day-to-day operations. Things like automated rent collection, tenant communication, maintenance tracking, financial reporting, ecommerce sales and even rent secured loan applications all from one place. All of this built to ease your operations and increase cashflow for you.

I\'m not trying to sell you anything today — I\'d love to set up a proper 20-minute demo so you can see it first-hand and decide if it\'s worth exploring further.

Would you be open to that? I can work around your schedule."


IF THEY ASK FOR MORE DETAIL NOW
────────────────────────────────
"Of course. The platform handles the full property management workflow — tenant onboarding, rent collection, maintenance requests, and reporting, vacant units ads, ecommerce sales from you to your tenants and easy loan features both for you and your tenants. Most of our clients say the biggest win is getting all of that out of spreadsheets and WhatsApp chats and into one organised system along with features that guarantee visibiity and increased cashflow.

For a {{property_type}} portfolio like {{company_name}}\'s, the biggest impact tends to be on organized and central management / increase vacancy visibility / increased cashflow.

That\'s actually what I\'d love to show you properly in a demo — would [day] or [day] work for a 20-minute call?"


IF THEY SAY THEY\'RE BUSY
─────────────────────────
"No problem at all. I completely understand. When would be a better time to call back? I can work around whenever suits you."

[Log the callback time in your notes below]


IF THEY SAY NOT INTERESTED
───────────────────────────
"I appreciate you being straight with me. Can I ask; is it more about timing, or is there something specific that makes it not a fit right now? Just so I understand.

[If timing] "That makes sense. Would it be okay if I reached out again in a few months when things might have settled down?"

[If something specific] "I appreciate that. I won\'t take up more of your time. Thanks for speaking with me {{contact_name}}."


VOICEMAIL SCRIPT
─────────────────
"Hi {{contact_name}}, this is {{affiliate_name}} calling about a property management platform that I think could be useful for {{company_name}}. I\'ll keep this brief — if you get a chance, I\'d love to connect for just 5 minutes. You can reach me on {{affiliate_phone}}. I\'ll try you again [day]. Thanks, have a great day."


NOTES AFTER THE CALL
─────────────────────
— Did they answer?
— What was their reaction / key objection?
— Next step agreed?
— Follow up date:',
  ),
  6 => 
  array (
    'category' => 'reengage',
    'action_type' => 'email',
    'name' => 'Email — Soft Re-engagement',
    'message_template' => 'Hi {{contact_name}},

I hope things have been going well at {{company_name}}. It\'s been a little while since we last connected, and I wanted to reach out — no pressure, just a genuine check-in.

A lot has changed on our platform since we last spoke. I\'d be willing to take you through some massive value additions for {{company_name}}

I understand timing doesn\'t always line up perfectly, and I wanted to make sure the door stays open if your situation has changed or if property management has come back onto your radar.

If you ever want to reconnect, I\'m happy to arrange a fresh demo or just have a conversation. Whichever is more useful.

Wishing you all the best regardless,

{{affiliate_name}}',
  ),
  7 => 
  array (
    'category' => 'reminder',
    'action_type' => 'whatsapp',
    'name' => 'WhatsApp Reminder — Demo in 12 Hours',
    'message_template' => 'Hi {{contact_name}} 👋

Quick reminder. Our demo is coming up in about 12 hours! Really looking forward to showing you what the platform can do for {{company_name}}.

[Add time and joining details here]

Any questions before we connect? Feel free to message me here.

See you soon!
{{affiliate_name}}',
  ),
  8 => 
  array (
    'category' => 'reminder',
    'action_type' => 'email',
    'name' => 'Email Reminder — Demo in 24 Hours',
    'message_template' => 'Hi {{contact_name}},

Just a friendly reminder that we have a demo scheduled for tomorrow. I\'m looking forward to showing you the platform and answering any questions you might have.

Here are the details:
- What: Product demo — {{company_name}}
- When: [insert time]
- How: [insert link or phone number]

If anything has come up and you need to reschedule, just reply to this message and we\'ll find a time that works better.

See you tomorrow!

{{affiliate_name}}',
  ),
  9 => 
  array (
    'category' => 'reminder',
    'action_type' => 'call',
    'name' => 'Call Script — Demo Confirmation 2 Hours',
    'message_template' => 'CALL SCRIPT — 2 HOUR DEMO CONFIRMATION

Purpose: Confirm attendance, resolve any last-minute issues.

Opening:
"Hi {{contact_name}}, this is {{affiliate_name}}. I\'m just calling to confirm our demo in about 2 hours — still on for you?"

If confirmed:
"Perfect! I\'ll have everything ready. We\'ll be covering [key areas relevant to {{company_name}}]. Should only take about 20 minutes."

If they need to reschedule:
"No problem at all — let\'s find a time that works. Are you free later today or would tomorrow suit you better?"

If no answer:
Leave a voicemail: "Hi {{contact_name}}, this is {{affiliate_name}} — just confirming our demo later today. Looking forward to it! Call me back on [number] if anything comes up."
Or call Back Later.',
  ),
  10 => 
  array (
    'category' => 'retention',
    'action_type' => 'whatsapp',
    'name' => 'WhatsApp Check-In — Converted Client',
    'message_template' => 'Hi {{contact_name}} 👋

It\'s {{affiliate_name}} — just dropping a quick message to check in 
on how things are going with the platform at {{company_name}}.

A few things I wanted to touch on:

📊 *Usage* — Is the team finding it useful day to day? Any areas 
you haven\'t fully explored or anything that isn\'t quite working the 
way you expected?

💰 *Cashflow tools* — Are you actively using the automated rent 
collection, arrears tracking and financial reports? These tend to 
have the biggest impact on revenue and are worth making sure are 
fully set up.

🚀 *New features* — There have been some useful updates released 
recently. I\'ll send over a summary shortly — there are a couple I 
think are particularly relevant for {{company_name}}.

No rush at all — whenever you get a moment, just reply here and we 
can go through anything that would be helpful.

As always, I\'m here if you need anything.

{{affiliate_name}}',
  ),
  11 => 
  array (
    'category' => 'retention',
    'action_type' => 'email',
    'name' => 'Email Check-In — Converted Client',
    'message_template' => 'Hi {{contact_name}},

I hope things are going well at {{company_name}}. I try to check in 
with all our clients on a regular basis — not to sell anything, just 
to make sure you\'re getting the most out of the platform and that 
everything is running the way it should be.

A few things I wanted to cover:


HOW IS THE PLATFORM WORKING FOR YOU?

Is the team finding it useful day to day? If there are any areas 
that feel clunky, underused, or not quite right for how {{company_name}} 
operates, I\'d love to know — it helps me make sure you\'re properly 
set up and supported.


ARE YOU MAKING FULL USE OF THE CASHFLOW TOOLS?

The features that tend to have the biggest direct impact on revenue 
are the automated rent collection, arrears tracking, and financial 
reporting tools. If these aren\'t fully set up or your team hasn\'t 
had a chance to explore them properly, it\'s genuinely worth 
prioritising. Most clients who activate these features see a 
noticeable improvement in on-time payments within the first month.

If you\'d like a short walkthrough of the financial side of the 
platform, just say the word and I\'ll arrange it.


WHAT\'S NEW ON THE PLATFORM

There have been some useful updates released recently that I think 
are relevant to {{company_name}}

If you\'d like a quick demo of them, I\'m happy to set something up.


ONE SMALL FAVOUR

If you know of other property managers who might benefit from the 
platform, an introduction would mean a lot. You\'ve seen first-hand 
what it can do — and a recommendation from someone in the industry 
always carries more weight than anything I can say.


As always, feel free to reply to this email or message me directly 
if anything comes up. I\'m here to make sure {{company_name}} gets 
real value from the platform.

Warm regards,
{{affiliate_name}}',
  ),
  12 => 
  array (
    'category' => 'retention',
    'action_type' => 'call',
    'name' => 'Call Script — Converted Client Check-In',
    'message_template' => 'CONVERTED CLIENT CHECK-IN — CALL SCRIPT
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

CONTEXT
───────
{{company_name}} is a paying customer. This is a relationship call, 
not a sales call. Your goal is to make sure they are getting genuine 
value, using the revenue-generating features of the platform, and 
feeling supported. A client who feels looked after renews — and 
renewing clients are your most reliable income.

Do not rush this call. Listen more than you speak.


OPENING
───────
"Hi {{contact_name}}, it\'s {{affiliate_name}} — just calling to check 
in on how things are going with the platform at {{company_name}}. 
Do you have 5 minutes?"


CHECK GENERAL USAGE
────────────────────
"How has the team been finding it day to day? Is it becoming part of 
the regular workflow or are there areas you haven\'t fully explored yet?"

IF STRONG USAGE:
"That\'s great to hear. Which parts of the system have made the 
biggest difference so far?"
→ Acknowledge it, then move to cashflow tools below.

IF LIGHT USAGE:
"That\'s useful to know. What tends to get in the way — is it more 
about time, training, or the system not quite fitting how your team 
works?"
→ Offer a short refresher session or to flag it to support.

IF ISSUES OR FRUSTRATIONS:
"I really appreciate you telling me that. Let me make sure that gets 
looked at properly — can I take a note of exactly what\'s happening 
so I can follow it up for you?"
→ Log it in your notes and escalate to admin if needed.


CASHFLOW — REVENUE GENERATING FEATURES
────────────────────────────────────────
"One thing I always like to check in on is whether clients are making 
full use of the cashflow side of the platform — things like automated 
rent collection, arrears tracking, and the financial reporting tools. 
These tend to have the most direct impact on revenue.

Is {{company_name}} using those features actively, or is that 
something we haven\'t fully set up yet?"

IF NOT USING THEM:
"That\'s actually worth prioritising — most of our clients see a 
noticeable improvement in on-time payments once the automated 
collection and reminder system is running. Would it be helpful if I 
arranged a short walkthrough specifically on the financial tools?"

IF USING THEM:
"Excellent. Are the reports giving you the visibility you need, or 
is there anything you wish you could see that isn\'t currently there?"


NEW FEATURES
─────────────
"The other reason I wanted to call is that there have been some new 
features released recently that I think are relevant to {{company_name}}.

[Insert current new features here before calling]

The one I think will be most useful for you specifically is [feature] 
— based on the way your team uses the system, it should [specific 
benefit]. Have you had a chance to explore that yet?"

IF NO:
"It\'s worth having a look — I can send you a quick overview after 
this call so you know what to look for. Or if it would be more 
useful, I can arrange a short demo of the new features with your team."


REFERRALS — OPTIONAL, READ THE ROOM
─────────────────────────────────────
"One last thing — if you know of other property managers who might 
benefit from the platform, I\'d love an introduction. You know better 
than anyone what it was like before and after, and sometimes a word 
from someone in the same industry carries a lot more weight than 
anything I can say."


CLOSING
────────
"Thanks so much for your time {{contact_name}}. It\'s really good to 
hear how things are going at {{company_name}}. I\'ll [follow up on 
any issues raised / send over the feature overview / arrange that 
walkthrough] — you should hear from me by [date].

Don\'t hesitate to reach out if anything comes up in the meantime."


VOICEMAIL SCRIPT
─────────────────
"Hi {{contact_name}}, it\'s {{affiliate_name}} — just calling for a 
quick monthly check-in on how the platform is going for {{company_name}}. 
Nothing urgent, just want to make sure everything is running well and 
share a couple of new features I think you\'ll find useful. Call me 
back on [your number] when you get a chance. Thanks."


NOTES AFTER THE CALL
─────────────────────
— Did they answer?
— General usage level: Strong / Moderate / Light
— Using cashflow features: Yes / No / Partially
— Issues or frustrations raised:
— New features shown or sent:
— Referral potential: Yes / No
— Next check-in date:
— Anything to escalate to admin:',
  ),
  13 => 
  array (
    'category' => 'trial',
    'action_type' => 'whatsapp',
    'name' => 'WhatsApp Check-In — During Trial',
    'message_template' => 'Hi {{contact_name}} 👋

Hope things are going well at {{company_name}}! Just checking in on your trial. How has the experience been so far?

- Is the platform doing what you hoped?
- Has your team had a chance to explore it?
- Anything you\'d like me to walk you through?

I\'m here if you need anything. And whenever you\'re ready to talk about converting to a full account, just let me know — happy to walk you through the options.

{{affiliate_name}}',
  ),
  14 => 
  array (
    'category' => 'trial_expired',
    'action_type' => 'whatsapp',
    'name' => 'WhatsApp — Trial Expired Follow-Up',
    'message_template' => 'Hi {{contact_name}} 👋

I noticed the trial period for {{company_name}} has just come to an end. I hope it gave your team a good feel for what the platform can do!

I wanted to reach out personally to see how it went and whether there\'s anything I can help with:

- Did the trial meet your expectations?
- Is there anything your team felt was missing?
- Would an extension help you make a final decision?

I\'m keen to make sure you have everything you need. What\'s the best next step from your side?

{{affiliate_name}}',
  ),
  15 => 
  array (
    'category' => 'trial_expired',
    'action_type' => 'call',
    'name' => 'Call Script — Trial Expired',
    'message_template' => 'TRIAL EXPIRED — CONVERSION CALL SCRIPT
━━━━━━━━━━━━━━━━━━━━━━

CONTEXT
───────
{{company_name}} has just come off a trial. This is the highest-converting 
call you can make — they already know the platform. Your job is to 
understand their experience and remove whatever is standing between 
them and a paid account.


OPENING
───────
"Hi {{contact_name}}, this is {{affiliate_name}}. I\'m calling because 
the trial account we set up for {{company_name}} recently came to an end 
and I wanted to personally check in. Do you have 5 minutes?"


GAUGE THE TRIAL EXPERIENCE
───────────────────────────
"How did the trial go overall? Did your team get a chance to explore 
the platform properly?"

[Listen carefully — their answer tells you everything]

IF POSITIVE:
"That\'s great to hear. What stood out most for you?"
→ Reflect it back: "So if [thing they mentioned] is already working 
  well in the trial, imagine having that running permanently with full 
  support behind it."

IF MIXED / UNCERTAIN:
"I appreciate you being honest. What felt like it was missing or 
didn\'t quite land for {{company_name}}?"
→ Address it specifically — don\'t brush past it.

IF THEY DIDN\'T USE IT MUCH:
"That\'s actually more common than you\'d think — trials can be hard 
to prioritise. Can I ask what got in the way? Because if it\'s a 
setup or time issue, that\'s something we can solve."


HANDLE THE MOST COMMON OBJECTIONS
───────────────────────────────────
"Need more time to decide"
→ "Completely fair. What would help you feel more confident? Would 
   a short extension make sense so your team can finish evaluating it 
   properly?"

"Need to discuss with the team / management"
→ "Of course. Would it help if I joined a quick call with them? I can 
   answer any technical or commercial questions directly and save you 
   having to relay everything."

"It\'s too expensive"
→ "I hear you. Can I ask — when you weigh it against the time your 
   team currently spends managing things manually, does the cost still 
   feel disproportionate? Most of our clients find it pays for itself 
   within the first couple of months."

"We\'re happy with our current system"
→ "Fair enough. Out of curiosity, what does your current setup handle 
   that the trial didn\'t match? I want to understand where we fell 
   short for {{company_name}} specifically."

"Not the right time"
→ "I understand — things get busy. Is this more about budget timing 
   or operational timing? Just so I know when to follow up in a way 
   that\'s actually useful for you."


THE ASK
────────
"Based on everything you\'ve seen, I genuinely think this is the right 
fit for {{company_name}}. The trial showed you what it can do — the 
next step is just making it permanent so your team doesn\'t lose the 
progress you\'ve already made.

Can we go ahead and get you set up on a paid account today?"


IF THEY NEED AN EXTENSION INSTEAD
───────────────────────────────────
"No problem at all — I\'d rather you make the right decision than a 
rushed one. Let me request a trial extension for you and we can 
reconnect at the end of it with a clearer picture.

What specifically do you want to evaluate during the extension so 
we can make sure you get what you need?"

[Log the extension reason in your notes and submit a trial extension 
request from the lead page when you\'re done with this call]


CLOSING — WHATEVER THE OUTCOME
────────────────────────────────
CONVERTING:
"Excellent — I\'ll get everything set up and you\'ll receive the account 
details shortly. Thank you for giving the platform a proper look 
{{contact_name}}, I think {{company_name}} is going to get a lot out 
of it."

EXTENDING:
"I\'ll get the extension requested today. Let\'s plan to speak again 
on [date] — does that work for you?"

LOST FOR NOW:
"I appreciate your time {{contact_name}}. If anything changes down 
the line or you want to revisit it, please don\'t hesitate to reach 
out. I\'ll check back in a few months if that\'s okay."


VOICEMAIL SCRIPT
─────────────────
"Hi {{contact_name}}, this is {{affiliate_name}} calling about the 
trial account that recently ended for {{company_name}}. I just wanted 
to check in on how it went and talk about next steps. Give me a call 
back on [your number] when you get a chance, or I\'ll try you again 
[day]. Thanks."


NOTES AFTER THE CALL
─────────────────────
— Did they answer?
— How did they rate the trial experience?
— Main objection raised:
— Outcome: Converting / Extending / Lost / Callback
— Follow up date:
— Anything to flag for admin (extension request, special pricing, etc.):',
  ),
  16 => 
  array (
    'category' => 'demo_complete',
    'action_type' => 'email',
    'name' => 'Email — Post-Demo Follow-Up',
    'message_template' => 'Hi {{contact_name}},

Thank you for taking the time to go through the demo — I hope it gave you a clear sense of how the platform could work for {{company_name}}.

I wanted to follow up in case any questions came up afterwards. Whether it\'s a specific feature, the cashflow tools, or how onboarding would work for your team, I\'m happy to clarify anything.

If you\'d like to explore it hands-on, I can arrange a sponsored trial account so your team can use the system with your own workflow — no commitment, just a chance to see how it fits.

What would be a good next step from your side?

{{affiliate_name}}',
  ),
  17 => 
  array (
    'category' => 'trial',
    'action_type' => 'email',
    'name' => 'Email Check-In — During Trial',
    'message_template' => 'Hi {{contact_name}},

I hope the trial is going well at {{company_name}}. I wanted to check in and make sure your team has everything they need to get a real feel for the platform.

A few quick things:
- Is the system doing what you hoped so far?
- Has the team explored the areas that matter most to you — rent collection, tenant communication, reporting?
- Anything you\'d like me to walk you through?

Whenever you\'re ready to talk about converting to a full account, I\'m happy to go through the options. And if an extension would help you evaluate it properly, just say the word.

{{affiliate_name}}',
  ),
  18 => 
  array (
    'category' => 'trial',
    'action_type' => 'call',
    'name' => 'Call Script — During Trial',
    'message_template' => 'CALL SCRIPT — DURING-TRIAL CHECK-IN

Purpose: make sure the trial is being used, surface blockers, keep the path to conversion open.

Opening:
"Hi {{contact_name}}, it\'s {{affiliate_name}}. Just checking in on how the trial is going for {{company_name}} — do you have a couple of minutes?"

Gauge usage:
"Has the team had a chance to get into it? Which parts have you explored so far?"

If strong usage:
"That\'s great — what\'s stood out most? If [feature] is already useful in the trial, that\'s exactly what it looks like running permanently with full support."

If light usage:
"That\'s common — trials can be hard to prioritise. What\'s getting in the way: time, setup, or training? Most of that we can solve quickly."

Surface blockers:
"Is there anything that hasn\'t worked the way you expected, or anything missing for how {{company_name}} operates?"

Soft close:
"Whenever you\'re ready, converting to a full account keeps everything your team has set up. Would it help if I walked you through the options — or arranged a short extension so you can finish evaluating it?"

Log the outcome + next step in your notes.',
  ),
  19 => 
  array (
    'category' => 'reengage',
    'action_type' => 'whatsapp',
    'name' => 'WhatsApp — Soft Re-engagement',
    'message_template' => 'Hi {{contact_name}} 👋

It\'s {{affiliate_name}} — it\'s been a little while since we last connected about {{company_name}}. No pressure at all, just a genuine check-in.

Quite a bit has been added to the platform since we last spoke, and a couple of the updates feel especially relevant to you.

If property management has come back onto your radar, I\'d be happy to arrange a fresh demo or just have a quick chat — whichever is more useful.

Wishing you well either way.

{{affiliate_name}}',
  ),
  20 => 
  array (
    'category' => 'reengage',
    'action_type' => 'call',
    'name' => 'Call Script — Soft Re-engagement',
    'message_template' => 'CALL SCRIPT — SOFT RE-ENGAGEMENT

Context: {{company_name}} went quiet or was lost/expired. Low-pressure, door-open call — not a hard sell.

Opening:
"Hi {{contact_name}}, it\'s {{affiliate_name}}. It\'s been a while since we spoke about the property management platform — I\'m not calling to push anything, just to check in. Do you have a moment?"

Reconnect:
"How have things been at {{company_name}}? Last time the timing wasn\'t quite right — I wanted to see whether anything has changed on your side."

If open:
"A lot has been added since — [mention one relevant update]. Would it be worth a fresh 20-minute demo so you can see where it\'s at now?"

If still not the time:
"Completely understand. Is it more budget timing or operational timing? Just so I know when a follow-up would actually be useful."

Close:
"No problem at all — I\'ll leave the door open. Would it be okay if I checked back in a few months? And if anything changes before then, you have my number."

Log the callback window in your notes.',
  ),
  21 => 
  array (
    'category' => 'trial_expired',
    'action_type' => 'email',
    'name' => 'Email — Trial Expired Follow-Up',
    'message_template' => 'Hi {{contact_name}},

I noticed the trial account we set up for {{company_name}} has just come to an end — I wanted to reach out personally rather than let it lapse quietly.

The end of a trial is really the best moment to decide, because your team has already seen how the platform fits your workflow. So I\'d love to know:
- How did the trial go overall?
- Was there anything missing, or anything your team wanted more time with?
- Would a short extension help you make a confident decision?

If you\'re ready, I can move you onto a full account today so you don\'t lose the setup you\'ve already built. And if an extension makes more sense, I\'m happy to arrange that instead.

What\'s the best next step from your side?

{{affiliate_name}}',
  ),
);
