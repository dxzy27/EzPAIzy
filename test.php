<?php
$key='sk-pNHjheb9aJamXXwjmh6ekYsXEdZzk';
$ch=curl_init('https://api.apifree.ai/v1/chat/completions');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer '.$key]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['model'=>'openai/chatgpt-5.2', 'messages'=>[['role'=>'user','content'=>'hi']]]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
echo "chatgpt-5.2: " . curl_exec($ch) . "\n";
