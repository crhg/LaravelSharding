<?php

namespace Crhg\LaravelSharding\Exceptions;

/**
 * Shardが決定していない状態でshardを指定しないと行えない操作をしようとした例外
 */
class UnambiguousShardException extends \RuntimeException
{

}
