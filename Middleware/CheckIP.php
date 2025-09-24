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
        gp247_report('ipList: ' . json_encode($ipList));
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

    protected function ipList()
    {
        $r = request();
        $list = [];

        // Collect CF-Connecting-IP
        $cf = $r->header('CF-Connecting-IP');
        if (!empty($cf)) {
            $list[] = trim($cf);
        }

        // Collect X-Forwarded-For (may contain comma separated values)
        $xff = $r->header('X-Forwarded-For');
        if (!empty($xff)) {
            foreach (explode(',', $xff) as $item) {
                $item = trim($item);
                if ($item !== '') {
                    $list[] = $item;
                }
            }
        }

        // Collect request IP chain
        foreach ((array) $r->ips() as $ip) {
            if (!empty($ip)) {
                $list[] = $ip;
            }
        }

        // De-duplicate while preserving order
        return array_values(array_unique($list));
    }
}
