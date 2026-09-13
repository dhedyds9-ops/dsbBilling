<?php
$file = "resources/views/livewire/isp/service-profile/index.blade.php";
$content = file_get_contents($file);

$ifs = preg_match_all("/@if\b/", $content);
$endifs = preg_match_all("/@endif\b/", $content);
$foreach = preg_match_all("/@foreach\b/", $content);
$endforeach = preg_match_all("/@endforeach\b/", $content);
$forelse = preg_match_all("/@forelse\b/", $content);
$endforelse = preg_match_all("/@endforelse\b/", $content);
$for = preg_match_all("/@for\b/", $content);
$endfor = preg_match_all("/@endfor\b/", $content);
$while = preg_match_all("/@while\b/", $content);
$endwhile = preg_match_all("/@endwhile\b/", $content);
$section = preg_match_all("/@section\b/", $content);
$endsection = preg_match_all("/@endsection\b/", $content);
$hasSection = preg_match_all("/@hasSection\b/", $content);
$show = preg_match_all("/@show\b/", $content);

echo "INDEX BLADE:\n";
echo "@if: $ifs, @endif: $endifs, diff: ".($ifs-$endifs)."\n";
echo "@foreach: $foreach, @endforeach: $endforeach, diff: ".($foreach-$endforeach)."\n";
echo "@forelse: $forelse, @endforelse: $endforelse, diff: ".($forelse-$endforelse)."\n";
echo "@for: $for, @endfor: $endfor\n";
echo "@while: $while, @endwhile: $endwhile\n";
echo "@section: $section, @endsection: $endsection\n";
echo "@hasSection: $hasSection, @show: $show\n";
