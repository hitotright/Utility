<?php
/**
 * Created by PhpStorm.
 * User: hitotright
 * Date: 2020/3/6
 * Time: 16:26
 */

class Library_ThrottleRequests
{

    /**
     * The rate limiter instance.
     *
     */
    protected $limiter;
    protected $user_id;

    /**
     * Create a new request throttler.
     *
     * @return void
     */
    public function __construct(Library_RateLimiter $limiter,$user_id)
    {
        $this->limiter = $limiter;
        $this->user_id = $user_id ;//Session::instance()->get("xms_admin_user_id");
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure  $next
     * @param  int|string  $maxAttempts
     * @param  float|int  $decayMinutes
     *
     */
    public function handle(Request $request, Response $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $response = $this->buildException($next,$key, $maxAttempts,$decayMinutes);
            (new Bjui())->setMessage($response)->ajaxError();exit;
        }

        $this->limiter->hit($key, $decayMinutes);

        return $this->addHeaders(
            $next, $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    /**
     * Resolve the number of attempts if the user is authenticated or not.
     *
     * @param  int|string  $maxAttempts
     * @return int
     */
    protected function resolveMaxAttempts($request, $maxAttempts)
    {
        return (int) $maxAttempts;
    }

    /**
     * Resolve request signature.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function resolveRequestSignature(Request $request)
    {
        if ($url = $request->url()) {
            return sha1($url."|".$this->user_id);
        }

        throw new RuntimeException('Unable to generate the request signature. Route unavailable.');
    }

    /**
     * Create a 'too many attempts' exception.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     */
    protected function buildException(Response $response,$key, $maxAttempts,$decayMinutes)
    {
        $retryAfter = $this->getTimeUntilNextRetry($key,$decayMinutes * 60);
        $headers = $this->getHeaders(
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts, $retryAfter),
            $retryAfter
        );

        $response->headers($headers);
        return "访问频率超限！请等待".$retryAfter."秒";
    }

    /**
     * Get the number of seconds until the next retry.
     *
     * @param  string  $key
     * @return int
     */
    protected function getTimeUntilNextRetry($key,$decay)
    {
        return $decay - $this->limiter->availableIn($key);
    }

    /**
     * Add the limit header information to the given response.
     *
     * @param  int  $maxAttempts
     * @param  int  $remainingAttempts
     * @param  int|null  $retryAfter
     */
    protected function addHeaders(Response $response, $maxAttempts, $remainingAttempts, $retryAfter = null)
    {
        $response->headers(
            $this->getHeaders($maxAttempts, $remainingAttempts, $retryAfter)
        );

        return $response;
    }

    /**
     * Get the limit headers information.
     *
     * @param  int  $maxAttempts
     * @param  int  $remainingAttempts
     * @param  int|null  $retryAfter
     * @return array
     */
    protected function getHeaders($maxAttempts, $remainingAttempts, $retryAfter = null)
    {
        $headers = [
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ];

        if (! is_null($retryAfter)) {
            $headers['Retry-After'] = $retryAfter;
            $headers['X-RateLimit-Reset'] = time()+$retryAfter;
        }

        return $headers;
    }

    /**
     * Calculate the number of remaining attempts.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @param  int|null  $retryAfter
     * @return int
     */
    protected function calculateRemainingAttempts($key, $maxAttempts, $retryAfter = null)
    {
        if (is_null($retryAfter)) {
            return $this->limiter->retriesLeft($key, $maxAttempts);
        }

        return 0;
    }
}
