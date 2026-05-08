<?php

namespace Interfaces\Observer;
interface IObserver{
    public function update(string $event, array $data): void;
}