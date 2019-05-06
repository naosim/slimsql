<?php
namespace Naosim\Lib;

use Psr\Http\Message\ServerRequestInterface as Request;
use Naosim\Lib\ValidationErrorException;

/**
 * リクエストからパラメータを取得する for slim
 * 
 */
class RequestGetter {
  /** @var Request */
  private $request;
  function __construct(Request $request) {
    $this->request = $request;
  }

  function getOptional(string $key): ?string {
    $list = [
      function($request, $key) {
        $queryParams = $request->getQueryParams();
        if($queryParams && isset($queryParams[$key])) {
          return $queryParams[$key];
        }
        return null;
      },
      function($request, $key) {
        $parsedBody = $request->getParsedBody();
        if($parsedBody && isset($parsedBody[$key])) {
          return $parsedBody[$key];
        }
        return null;
      },
      function($request, $key) {
        if($request->hasHeader($key)) {
          return $this->request->getHeader($key);
        }
        return null;
      }
    ];

    foreach($list as $getter) {
      $value = $getter($this->request, $key);
      if($value !== null) {
        return trim($value);
      }
    }

    return null;
  }

  function getRequired(string $key): string {
    $value = $this->getOptional($key);
    if($value === null) {
      throw new ValidationErrorException("param not found: $key");
    }
    return $value;
  }

  function getOrDefault(string $key, string $default): ?string {
    $value = $this->getOptional($key);
    return $value !== null ? $value : $default;
  }

  /**
   * @param string $key
   * @param ()->RuntimeException $default
   */
  function getOrThrow(string $key, $default): string {
    $value = $this->getOptional($key);
    if($value === null) {
      throw $default();
    }
    return $value;
  }
}
