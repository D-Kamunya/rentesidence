CENTRESIDENCE DIGITAL BUSINESS CARD

1. Copy card.blade.php to:
   resources/views/card.blade.php

2. Add the contents of routes-web.php to routes/web.php.
   If web.php already imports Response, do not duplicate that import.

3. Clear Laravel caches if necessary:
   php artisan optimize:clear

4. Visit:
   https://centresidence.com/card

5. The QR code points to:
   https://centresidence.com/card

QR FILE:
centresidence-card-qr.png

The QR can be printed or shown on a phone. It does not need to be regenerated
when the card page is redesigned later, as long as /card remains the URL.
