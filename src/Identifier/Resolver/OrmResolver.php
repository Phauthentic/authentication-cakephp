<?php
declare(strict_types=1);
namespace Phauthentic\Authentication\Identifier\Resolver;

use ArrayAccess;
use Authentication\Identifier\Resolver\ResolverInterface;
use Cake\Core\InstanceConfigTrait;
use Cake\ORM\Locator\LocatorAwareTrait;

class OrmResolver implements ResolverInterface
{

    use InstanceConfigTrait;
    use LocatorAwareTrait;

    /**
     * Default configuration.
     * - `userModel` The alias for users table, defaults to Users.
     * - `finder` The finder method to use to fetch user record. Defaults to 'all'.
     *   You can set finder name as string or an array where key is finder name and value
     *   is an array passed to `Table::find()` options.
     *   E.g. ['finderName' => ['some_finder_option' => 'some_value']]
     *
     * @var array
     */
    protected $_defaultConfig = [
        'userModel' => 'Users',
        'finder' => 'all',
    ];

    protected $_config;

    /**
     * Constructor.
     *
     * @param array $config Config array.
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
    }

    /**
     * {@inheritDoc}
     */
    public function find(array $conditions): ?ArrayAccess
    {
        $table = $this->getTableLocator()->get($this->getConfig('userModel'));

        $query = $table->query();
        $finders = (array)$this->getConfig('finder');
        foreach ($finders as $finder => $options) {
            if (is_string($options)) {
                $query->find($options);
            } else {
                $query->find($finder, $options);
            }
        }

        $where = [];
        foreach ($conditions as $field => $value) {
            $field = $table->aliasField($field);
            if (is_array($value)) {
                $field = $field . ' IN';
            }
            $where[$field] = $value;
        }

        return $query->where($where)->first();
    }
}
