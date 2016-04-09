#!/bin/bash
indexer --all --rotate --quiet
php rt_truncate.php