#!/usr/bin/env php
<?php

/public static function removeGitDirectories() {
    $root = static::getDrupalRoot(getcwd());
    exec('find ' . $root . ' -name \'.git\' | xargs rm -rf');
  }
