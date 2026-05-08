<?php
namespace Interfaces\Observer;
interface ISubject{
    public function registerObserver(IObserver $o): void;
    public function removeObserver(IObserver $o): void;
    public function notifyObservers(string $event, array $data): void;
}
