<?php

namespace Bestkit\Filter;

use Bestkit\Foundation\ValidationException as BestkitValidationException;
use Bestkit\Locale\Translator;

trait ValidateFilterTrait
{
    /**
     * @throws BestkitValidationException
     * @return array<string>|array<array>
     */
    protected function asStringArray($filterValue, bool $multidimensional = false): array
    {
        if (is_array($filterValue)) {
            $value = array_map(function ($subValue) use ($multidimensional) {
                if (is_array($subValue) && ! $multidimensional) {
                    $this->throwValidationException('core.api.invalid_filter_type.must_not_be_multidimensional_array_message');
                } elseif (is_array($subValue)) {
                    return $this->asStringArray($subValue, true);
                } else {
                    return $this->asString($subValue);
                }
            }, $filterValue);
        } else {
            $value = explode(',', $this->asString($filterValue));
        }

        return $value;
    }

    /**
     * @throws BestkitValidationException
     */
    protected function asString($filterValue): string
    {
        if (is_array($filterValue)) {
            $this->throwValidationException('core.api.invalid_filter_type.must_not_be_array_message');
        }

        return trim($filterValue, '"');
    }

    /**
     * @throws BestkitValidationException
     */
    protected function asInt($filterValue): int
    {
        if (! is_numeric($filterValue)) {
            $this->throwValidationException('core.api.invalid_filter_type.must_be_numeric_message');
        }

        return (int) $this->asString($filterValue);
    }

    /**
     * @throws BestkitValidationException
     * @return array<int>
     */
    protected function asIntArray($filterValue): array
    {
        return array_map(function ($value) {
            return $this->asInt($value);
        }, $this->asStringArray($filterValue));
    }

    /**
     * @throws BestkitValidationException
     */
    protected function asBool($filterValue): bool
    {
        return $this->asString($filterValue) === '1';
    }

    /**
     * @throws BestkitValidationException
     */
    private function throwValidationException(string $messageCode): void
    {
        $translator = resolve(Translator::class);

        throw new BestkitValidationException([
            'message' => $translator->trans($messageCode, ['{filter}' => $this->getFilterKey()]),
        ]);
    }
}
