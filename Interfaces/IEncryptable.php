<?php
interface IEncryptable {
    public function encrypt(): string;
    public function decrypt(string $key): string;
}