<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Setting;

/**
 * SettingRepository
 *
 * Custom repository for Setting class.
 * Specially for methods getSetting & setSetting
 */
class SettingRepository extends \Doctrine\ORM\EntityRepository
{

    /**
     * Retrieves setting row from db by `skey` parameter
     * Returns string setting value or array with setting object(for method setSetting)
     *
     * @param string $key
     * @param string $default
     * @param bool $object
     * @return array|string
     */
    public function getSetting($key = '', $default = '', $object = false)
    {
        if (!$key) {
            return $default;
        }

        $q = $this->createQueryBuilder('s')
            ->where('s.skey = :key')
            ->setParameter('key', $key)
            ->setMaxResults(1)
            ->getQuery();
        $item = $q->getResult();

        if ($object) {
            return $item;
        }

        if (empty($item)) {
            return $default;
        }
        return $item[0]->getValue();
    }

    /**
     * Creates new setting row or update existing (if it could be find with getSetting)
     *
     * @param string $key
     * @param string $value
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function setSetting($key = '', $value = '')
    {
        if (!$key) {
            return;
        }

        $setting = $this->getSetting($key, [], true);
        if (!empty($setting)) {
            // we have setting?
            $setting = $setting[0];
        } else {
            // ok, let's crate new row
            $setting = new Setting();
            $setting->setSkey($key);
        }
        $setting->setValue($value);

        $this->getEntityManager()->getConnection()->beginTransaction();
        try {
            $this->getEntityManager()->persist($setting);
            $this->getEntityManager()->flush();
            $this->getEntityManager()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getEntityManager()->getConnection()->rollBack();
        }
    }
}
