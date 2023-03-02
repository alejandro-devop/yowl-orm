<?php

namespace Alejodevop\YowlOrm\Exceptions;

/**
 * Exception thrown when a query is trying to be executed but no query was set.
 * @author Alejandro Quiroz <alejandro.devop@gmail.com>
 * @version 1.0.0
 * @since 1.0.0
 */
class DBNoQueryToBeExecuted extends \Exception {}