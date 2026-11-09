{{-- Meta (Facebook) Pixel — fires ONLY on the live centresidence.com host, so it never
     pollutes the ad-conversion data from the dev machine, ngrok tunnels, or the Solidus
     subdomain — and it works regardless of how prod's APP_ENV happens to be set. Loaded on
     the public funnel surfaces (landing, auth/register, marketing, blog), not deep dashboards. --}}
@php($__pixelHost = request()->getHost())
@if($__pixelHost === 'centresidence.com' || $__pixelHost === 'www.centresidence.com')
<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '1676644640779483');
fbq('track', 'PageView');
@if(session('fb_complete_registration'))
fbq('track', 'CompleteRegistration');
@endif
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=1676644640779483&ev=PageView&noscript=1"
/></noscript>
@if(session('fb_complete_registration'))
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=1676644640779483&ev=CompleteRegistration&noscript=1"
/></noscript>
@endif
<!-- End Meta Pixel Code -->
@endif
