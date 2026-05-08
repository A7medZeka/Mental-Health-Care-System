<?php
interface IStateMachine {
    public function getState(): string;
    public function transition(string $newState): bool;
}