<?php
echo strlen("VALUE:" . base64_encode(serialize(['echo'=>'test-payload'])) . "\n");
