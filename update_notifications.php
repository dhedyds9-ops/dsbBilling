<?php
$file = 'D:/dsBilling/app/Livewire/Crm/Customer/Customer360.php';
$content = file_get_contents($file);

$oldCode = '$notifications = \App\Models\Notification\Notification::where(\'recipient_id\', $this->customer->user_id)->orderBy(\'created_at\', \'desc\')->get();';
$newCode = '$notifications = \App\Models\Notification\Notification::where(\'recipient_id\', $this->customer->user_id)->orderBy(\'created_at\', \'desc\')->get()->map(function($notif) {
                return [
                    \'title\' => $notif->title,
                    \'message\' => $notif->message,
                    \'created_at\' => $notif->created_at,
                    \'read\' => $notif->status === \'read\'
                ];
            });';

$content = str_replace($oldCode, $newCode, $content);
file_put_contents($file, $content);
echo "Updated notifications mapping.";
?>
