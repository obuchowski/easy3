<?php

file_put_contents('/app/worker/test.txt', 'test' . PHP_EOL, FILE_APPEND | LOCK_EX);
