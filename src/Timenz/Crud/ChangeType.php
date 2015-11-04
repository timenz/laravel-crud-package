<?php

namespace Timenz\Crud;

abstract class ChangeType{

    const HIDDEN = 'hidden';
    const NUMERIC = 'numeric';
    const DECIMAL = 'decimal';
    const DATE = 'date';
    const DATETIME = 'datetime';
    const ENUM = 'enum';
    const SELECT = 'select';
    const TEXT = 'text';
    const IMAGE = 'image';
    const FILE = 'file';
    const MONEY = 'money';
}