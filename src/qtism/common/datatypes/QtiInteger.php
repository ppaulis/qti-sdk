<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\common\datatypes;

use qtism\common\enums\Cardinality;
use qtism\common\enums\BaseType;
use \InvalidArgumentException;

/**
 * Represents the Integer QTI datatype.
 *
 * From IMS QTI:
 *
 * An integer value is a whole number in the range [-2147483648,2147483647].
 * This is the range of a twos-complement 32-bit integer.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class QtiInteger extends QtiScalar
{
    /**
     * Checks whether or not $value is an integer compliant
     * with the QTI Integer datatype. Will check the range to make sure
     * its contained into [-2147483648,2147483647].
     *
     * @throws \InvalidArgumentException If $value is not an integer value compliant with the QTI Integer datatype.
     */
    protected function checkType($value)
    {
        if (Utils::isQtiInteger($value) !== true) {
            $msg = "The Integer Datatype only accepts to store integer values.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the baseType of the value. This method systematically
     * returns the BaseType::INTEGER value.
     *
     * @return integer A value from the BaseType enumeration.
     */
    public function getBaseType()
    {
        return BaseType::INTEGER;
    }

    /**
     * Get the cardinality of the value. This method systematically returns
     * the Cardinality::SINGLE value.
     */
    public function getCardinality()
    {
        return Cardinality::SINGLE;
    }

    public function __toString()
    {
        return '' . $this->getValue();
    }
}
