<?php
namespace Naosim\Lib;

use Slim\App;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use RuntimeException;

class ApiTokenAuth {

  private $app;

  /** @var string */
  private $apiKeyWord;

  /** @var ApiTokenAccountRepository */
  private $apiTokenAccountRepository;

  function __construct(
    App $app, string 
    $apiKeyWord, 
    ApiTokenAccountRepository $apiTokenAccountRepository
  ) {
    $this->app = $app;
    $this->apiKeyWord = $apiKeyWord;
    $this->apiTokenAccountRepository = $apiTokenAccountRepository;
  }

  public function __invoke(Request $request, Response $response, callable $next): Response {
      $req = new RequestGetter($request);
      $value = $req->getRequired($this->apiKeyWord);
      $apiToken = new ApiTOken($value);
      if($this->apiTokenAccountRepository->notHas($apiToken)) {
          throw new ValidationErrorException("'$this->apiKeyWord' not found. Set '$this->apiKeyWord' in your request");
      }

      $path = $request->getUri()->getPath();
      $accountName = $this->apiTokenAccountRepository->get($apiToken)->getApiTokenUserName()->getValue();
      $this->app->getContainer()->get('logger')->info(json_encode(['client'=>$accountName, 'path'=>$path]));

      $response = $next($request, $response);

      return $response;
  }
}

class StringVo {
  protected $value;
  function __construct(string $value) {
    $this->value = $value;    
  }
  function getValue() {
    return $this->value;
  }
}

class ApiToken extends StringVo {
  function eq(ApiToken $other): bool {
    return $this->value === $other->value;
  }
}

class ApiTokenUserName extends StringVo {
}

class ApiTokenAccount {
  
  protected $tokenUserName;
  protected $token;
  function __construct(ApiTokenUserName $tokenUserName, ApiToken $token) {
    $this->tokenUserName = $tokenUserName;    
    $this->token = $token;    
  }

  function isSameToken(ApiToken $apiToken) {
    return $this->token->eq($apiToken);
  }

  function getApiTokenUserName() {
    return $this->tokenUserName;
  }
}

interface ApiTokenAccountRepository {
  function has(ApiToken $apiToken): bool;
  function notHas(ApiToken $apiToken): bool;
  function get(ApiToken $apiToken): ApiTokenAccount;
}

class ApiTokenAccountRepositoryImpl implements ApiTokenAccountRepository {
  /** @var array $list ApiTokenAccount[] */
  private $list;

  function __construct(array $apiTokenAccounts) {
    $list = [];
    foreach($apiTokenAccounts as $apiTokenAccount) {
      $list[] = new ApiTokenAccount(new ApiTokenUserName($apiTokenAccount['user_name']), new ApiToken($apiTokenAccount['token']));
    }
    $this->list = $list;
  }

  function has(ApiToken $apiToken): bool {
    foreach($this->list as $apiTokenAccount) {
      if($apiTokenAccount->isSameToken($apiToken)) {
        return true;
      }
    }
    return false;
  }

  function notHas(ApiToken $apiToken): bool {
    return !$this->has($apiToken);
  }

  function get(ApiToken $apiToken): ApiTokenAccount {
    foreach($this->list as $apiTokenAccount) {
      if($apiTokenAccount->isSameToken($apiToken)) {
        return $apiTokenAccount;
      }
    }
    throw new RuntimeException('ApiTokenAccount not found');
  }
  
}