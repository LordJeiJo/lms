<?php
declare(strict_types=1);
session_start();
function current_user(){ return $_SESSION['user'] ?? null; }
function require_login(){ if(!current_user()){ header("Location: ?a=login"); exit; } }
function require_role($roles){ $u=current_user(); if(!$u||!in_array($u['role'], (array)$roles,true)){ http_response_code(403); echo "No autorizado"; exit; } }
function flash_set($msg,$type='ok'){ $_SESSION['flash']=['msg'=>$msg,'type'=>$type]; }
function flash_get(){ if(!empty($_SESSION['flash'])){ $f=$_SESSION['flash']; unset($_SESSION['flash']); return $f; } return null; }
?>