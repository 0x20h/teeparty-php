<?php
namespace Teeparty;

/**
 * Compatibility interface for PHP < 5.4
 */
interface JsonSerializable {
    public function jsonSerialize();
}
