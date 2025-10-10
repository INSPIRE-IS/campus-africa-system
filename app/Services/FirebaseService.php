<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\AuthException;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $firestore;
    protected $auth;

    public function __construct()
    {
        // $factory = (new Factory)->withServiceAccount(storage_path('app/firebase/serviceAccountKey.json'));
        // $this->auth = $factory->createAuth();
        // $this->firestore = $factory->createFirestore()->database();
        // $firestore = new FirestoreClient(['projectId' => 'campus-africa-bda4b', 'credentials' => storage_path('app/firebase/serviceAccountKey.json')]);
        $firebaseConfig = json_decode(env('FIREBASE_CREDENTIALS'), true);
        //Log::info('firebaseConfig', $firebaseConfig);

        $factory = (new Factory)->withServiceAccount($firebaseConfig);

        $this->auth = $factory->createAuth();
        $this->firestore = $factory->createFirestore()->database();

        // Optional direct FirestoreClient
        $firestore = new FirestoreClient([
            'projectId' => $firebaseConfig['project_id'],
            'credentials' => $firebaseConfig
        ]);
    }

    public function getAuth(): \Kreait\Firebase\Auth
    {
        return $this->auth;
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

    public function getUserByUid($value)
    {
        return $this->auth->getUser($value);
    }

    public function getUserByEmailOrUid($value)
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return $this->auth->getUserByEmail($value);
        }

        return $this->auth->getUser($value);
    }

    public function getUserProfileByUid(string $uid): ?array
    {
        $doc = $this->firestore->collection('users')->document($uid)->snapshot();

        if ($doc->exists()) {
            return $doc->data();
        }

        return null;
    }

    // ✅ 2. Register a User
    public function registerUser(array $details = [], array $extra = [])
    {
        // 1. Register in Firebase Auth
        $user = $this->auth->createUser(array_merge([
            'email' => $details['email'],
            'password' => $details['password'],
        ], $extra));

        // 2. Prepare additional Firestore fields
        $this->firestore->collection('users')->document($user->uid)->set([
            'uid' => $user->uid,
            'email' => $details['email'],
            'first_name' => $details['first_name'] ?? null,
            'last_name' => $details['last_name'] ?? null,
            'bed_code' => $details['bed_code'] ?? null,
            'role' => $details['role'] ?? null,
            'groups' => $details['groups'] ?? [],
            'created_time' => now()->toDateTimeString(),
        ]);

        // 3. Return the created Firebase Auth user
        return $user;
    }

    public function registerOrUpdateUser(array $details = [], array $extra = [])
    {
        try {
            // Check if user exists
            $existingUser = $this->auth->getUserByEmail($details['email']);
            Log::info('✅ User exists. ' . $existingUser->uid);
            // 🔁 Update Auth user
            $user = $this->auth->updateUser($existingUser->uid, array_merge([
                'password' => $details['password'],
            ], $extra));
        } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
            // 👤 Create new Auth user
            $user = $this->auth->createUser(array_merge([
                'email' => $details['email'],
                'password' => $details['password'],
            ], $extra));
        }

        // 📝 Create/update Firestore user document
        $this->firestore->collection('users')->document($user->uid)->set([
            'uid' => $user->uid,
            'email' => $details['email'],
            'first_name' => $details['first_name'] ?? null,
            'last_name' => $details['last_name'] ?? null,
            'bed_code' => $details['bed_code'] ?? null,
            'role' => $details['role'] ?? null,
            'groups' => $details['groups'] ?? [],
            'created_time' => now()->toDateTimeString(),
        ]);
        Log::info('✅ User exists. ' . $user->uid);
        return $user;
    }

    // ✅ 3. Update User Email
    public function updateEmail(string $uid, string $newEmail)
    {
        return $this->auth->updateUser($uid, ['email' => $newEmail]);
    }

    // ✅ 3. Update User Password
    public function updatePassword(string $uid, string $newPassword)
    {
        return $this->auth->updateUser($uid, ['password' => $newPassword]);
    }

    // ✅ 4. Get "maintenance_items" collection (paginated)
    public function getMaintenanceItems(int $limit = 10, string $startAfter)
    {
        $collection = $this->firestore->collection('maintenance_items')->orderBy('created_at', 'DESC')->limit($limit);

        if ($startAfter !== null && $startAfter !== '') {
            $snapshot = $this->firestore->collection('maintenance_items')->document($startAfter)->snapshot();
            $collection = $collection->startAfter($snapshot);
        }

        return $collection->documents();
    }
}
