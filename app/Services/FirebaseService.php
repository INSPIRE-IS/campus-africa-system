<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\AuthException;
use Google\Cloud\Firestore\FirestoreClient;

class FirebaseService
{
    protected $firestore;
    protected $auth;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(storage_path('app/firebase/serviceAccountKey.json'));

        $this->auth = $factory->createAuth();
        $this->firestore = $factory->createFirestore()->database();
        $firestore = new FirestoreClient(['projectId' => 'campus-africa-bda4b', 'credentials' => storage_path('app/firebase/serviceAccountKey.json')]);
    }

    // ✅ 1. View Firestore Structure Recursively
    public function getFirestoreStructure($limit = 5)
    {
        /** @disregard P1013 */
        $collections = $this->firestore->collections();

        $structure = [];

        foreach ($collections as $collection) {
            $sampleDocs = $collection->limit($limit)->documents()->rows();

            $fieldNames = [];

            foreach ($sampleDocs as $doc) {
                $data = $doc->data();
                $fieldNames = array_merge($fieldNames, array_keys($data));
            }

            // Remove duplicates
            $fieldNames = array_unique($fieldNames);

            $structure[] = [
                'collection' => $collection->id(),
                'sample_count' => count($sampleDocs),
                'sample_keys' => array_map(fn($doc) => $doc->id(), $sampleDocs),
                'field_names' => $fieldNames,
            ];
        }

        return $structure;
    }

    public function getFirestoreStructure1(): array
    {
        $structure = [];

        foreach ($this->firestore->collections() as $collection) {
            $structure[$collection->id()] = $this->mapCollection($collection);
        }

        return $structure;
    }

    public function getFirestoreStructure2($limit = 5)
    {
        /** @disregard P1013 */
        $collections = $this->firestore->collections();

        $structure = [];

        foreach ($collections as $collection) {
            $sampleDocs = $collection->limit($limit)->documents()->rows();

            $structure[] = [
                'collection' => $collection->id(),
                'sample_count' => count($sampleDocs),
                'sample_keys' => array_map(fn($doc) => $doc->id(), $sampleDocs),
            ];
        }

        return $structure;
    }


    private function mapCollection($collection): array
    {
        $results = [];

        foreach ($collection->documents() as $doc) {
            if ($doc->exists()) {
                $docData = [
                    'fields' => array_keys($doc->data()),
                    'subcollections' => [],
                ];

                foreach ($doc->reference()->collections() as $sub) {
                    $docData['subcollections'][$sub->id()] = $this->mapCollection($sub);
                }

                $results[$doc->id()] = $docData;
            }
        }

        return $results;
    }

    public function getUserByEmail($email)
    {
        return $this->auth->getUserByEmail($email);
    }

    // ✅ 2. Register a User
    public function registerUser(string $email, string $password, array $extra = [])
    {
        return $this->auth->createUser(array_merge([
            'email' => $email,
            'password' => $password,
        ], $extra));

        $this->firestore->collection('users')->document($user->uid)->set([
            'uid' => $user->uid,
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'residence' => $residence,
            'bed_code' => $bedCode,
            'role' => $role,
            'groups' => $groups,
            'created_time' => now()->toDateTimeString(),
        ]);
        return $user;
    }

    // ✅ 3. Update User Password
    public function updatePassword(string $uid, string $newPassword)
    {
        return $this->auth->updateUser($uid, ['password' => $newPassword]);
    }

    // ✅ 4. Get "maintenance_items" collection (paginated)
    public function getMaintenanceItems(int $limit = 10, string $startAfter = null)
    {
        $collection = $this->firestore->collection('maintenance_items')->orderBy('created_at', 'DESC')->limit($limit);

        if ($startAfter) {
            $snapshot = $this->firestore->collection('maintenance_items')->document($startAfter)->snapshot();
            $collection = $collection->startAfter($snapshot);
        }

        return $collection->documents();
    }
}
