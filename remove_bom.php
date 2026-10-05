<?php $p="app/Jobs/ACS/MassSyncGenieAcsJob.php"; $c=file_get_contents($p); if(substr($c,0,3)=="\xEF\xBB\xBF") file_put_contents($p, substr($c,3));
