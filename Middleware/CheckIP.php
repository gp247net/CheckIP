<?php

namespace App\GP247\Plugins\CheckIP\Middleware;

use Closure;

class CheckIP
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $ipList = $this->ipList();
        $ipsAllow = \App\GP247\Plugins\CheckIP\Models\CheckIPAccess::getIpsAllow();
        $ipsDeny = \App\GP247\Plugins\CheckIP\Models\CheckIPAccess::getIpsDeny();
        // Check if any IP in $ipList matches any IP in $ipsAllow, or if allow all ('*'), or is localhost
        if (
            in_array('127.0.0.1', $ipList) || 
            in_array('::1', $ipList) || 
            ($ipsAllow && (count(array_intersect($ipList, $ipsAllow)) > 0 || in_array('*', $ipsAllow)))
        ) {
            return $next($request);
        } 

        if ($ipsDeny && (count(array_intersect($ipList, $ipsDeny)) > 0 || in_array('*', $ipsDeny))) {
            // Find the first matching IP between $ipList and $ipsDeny
            $flatIpList = [];
            // Flatten $ipList to a simple array of IPs
            foreach ($ipList as $item) {
                if (is_array($item)) {
                    foreach ($item as $ip) {
                        $flatIpList[] = $ip;
                    }
                } elseif (!empty($item)) {
                    $flatIpList[] = $item;
                }
            }
            $firstBlockedIp = null;
            foreach ($flatIpList as $ip) {
                if (in_array($ip, $ipsDeny)) {
                    $firstBlockedIp = $ip;
                    break;
                }
            }
            // If no specific IP is found, check for the deny all ('*') case
            if (!$firstBlockedIp && in_array('*', $ipsDeny)) {
                $firstBlockedIp = '*';
            }
            abort(403, 'Your IP ' . $firstBlockedIp . ' blocked');
        }


        return $next($request);
    }

    /**
     * Resolve the request's client IP(s) used for allow/deny matching.
     *
     * WHY: only the framework-resolved IPs are trusted (Request::ips(), which
     * honours config/trustedproxy.php). Forwarded headers (X-Forwarded-For,
     * CF-Connecting-IP) are deliberately NOT read here: reading them directly let
     * any client spoof their address and bypass every deny rule (e.g. sending
     * "X-Forwarded-For: 127.0.0.1" matched the localhost allow branch and passed
     * unconditionally). To trust a real reverse proxy or Cloudflare, configure
     * TRUSTED_PROXIES so ips() returns the genuine client IP instead.
     *
     * @return array<int, string> Unique, framework-resolved client IP chain.
     */
    protected function ipList()
    {
        // De-duplicate while dropping empty entries.
        return array_values(array_unique(array_filter((array) request()->ips())));
    }
}
