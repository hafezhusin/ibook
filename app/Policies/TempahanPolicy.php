<?php

/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 * Hak cipta terpelihara. Dilarang meniru, menyalin, mengubah suai, atau
 * mengedar perisian ini tanpa kebenaran bertulis daripada pemilik hak cipta.
 *
 * Pembangun : Mohd Hafez bin Husin (Unit Aplikasi Gunasama)
 *
 * Unauthorized copying, modification, distribution, or use of this software,
 * via any medium, is strictly prohibited. Proprietary and confidential.
 */

namespace App\Policies;

use App\Models\Tempahan;
use App\Models\User;

class TempahanPolicy
{
    /**
     * Semua pengguna log masuk boleh lihat senarai tempahan.
     * Skop dikawal dalam controller (unitQuery).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Semua pengguna log masuk boleh lihat butiran mana-mana tempahan.
     * (Konsisten dengan kalendar yang memaparkan semua tempahan kepada semua pengguna.)
     * Hak sunting dikawal secara berasingan oleh policy update().
     */
    public function view(User $user, Tempahan $tempahan): bool
    {
        return true;
    }

    /**
     * Semua pengguna log masuk boleh buat tempahan baru.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Pengguna boleh kemaskini tempahan jika:
     * - Pentadbir / Urus Setia: semua tempahan
     * - Staf: tempahan sendiri atau rakan seunit (jabatan sama)
     */
    public function update(User $user, Tempahan $tempahan): bool
    {
        if (! $user->isStaf()) {
            return true;
        }

        return $tempahan->bolehDiEditOleh($user);
    }

    /**
     * Hanya Pentadbir Sistem boleh padam tempahan.
     */
    public function delete(User $user, Tempahan $tempahan): bool
    {
        return $user->isPentadbir();
    }

    /**
     * Pentadbir & Urus Setia boleh eksport.
     */
    public function export(User $user): bool
    {
        return $user->bolehLuluskan();
    }
}
