<?php
namespace app\common\middleware;

use Closure;

/**
 * 解析JSON请求体中间件
 */
class ParseJsonBody
{
    public function handle($request, Closure $next)
    {
        // 如果Content-Type是application/json，解析JSON body
        $contentType = $request->contentType();
        
        if ($contentType === 'application/json' || strpos($contentType, 'application/json') !== false) {
            $body = $request->getInput();
            if ($body) {
                $data = json_decode($body, true);
                if ($data && is_array($data)) {
                    // 将解析后的数据合并到请求参数中
                    $request->withInput($data);
                }
            }
        }
        
        return $next($request);
    }
}
