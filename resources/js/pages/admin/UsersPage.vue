<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'
import { 
  Search, Plus, Edit2, Trash2, Shield, X, Check, AlertCircle, 
  UserCheck, UserX, Loader2, Phone, Mail 
} from 'lucide-vue-next'

const users = ref([])
const roles = ref([])
const loading = ref(false)
const saving = ref(false)
const search = ref('')
const selectedRole = ref('')
const meta = ref({ current_page: 1, last_page: 1, total: 0, per_page: 15 })

const showModal = ref(false)
const editingUser = ref(null)
const errorMsg = ref('')
const successMsg = ref('')

const form = ref({
  name: '',
  email: '',
  password: '',
  phone: '',
  role: 'customer',
  is_active: true,
  role_ids: []
})

const fetchUsers = async (page = 1) => {
  loading.value = true
  try {
    const res = await api.get('/admin/users', {
      params: {
        page,
        search: search.value,
        role: selectedRole.value,
        per_page: meta.value.per_page
      }
    })
    if (res.data.success) {
      users.value = res.data.data
      meta.value = res.data.meta
    }
  } catch (err) {
    console.error('Failed to load users:', err)
  } finally {
    loading.value = false
  }
}

const fetchRoles = async () => {
  try {
    const res = await api.get('/admin/roles')
    if (res.data.success) {
      roles.value = res.data.data
    }
  } catch (err) {
    console.error('Failed to load roles:', err)
  }
}

const openCreateModal = () => {
  editingUser.value = null
  form.value = {
    name: '',
    email: '',
    password: '',
    phone: '',
    role: 'customer',
    is_active: true,
    role_ids: []
  }
  errorMsg.value = ''
  showModal.value = true
}

const openEditModal = (user) => {
  editingUser.value = user
  form.value = {
    name: user.name,
    email: user.email,
    password: '',
    phone: user.phone || '',
    role: user.role || 'customer',
    is_active: Boolean(user.is_active),
    role_ids: user.roles ? user.roles.map(r => r.id) : []
  }
  errorMsg.value = ''
  showModal.value = true
}

const saveUser = async () => {
  saving.value = true
  errorMsg.value = ''
  try {
    const payload = { ...form.value }
    if (editingUser.value && !payload.password) {
      delete payload.password
    }

    if (editingUser.value) {
      await api.put(`/admin/users/${editingUser.value.id}`, payload)
      successMsg.value = 'User updated successfully.'
    } else {
      await api.post('/admin/users', payload)
      successMsg.value = 'User created successfully.'
    }

    showModal.value = false
    fetchUsers(meta.value.current_page)
    setTimeout(() => { successMsg.value = '' }, 3000)
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to save user. Check requirements.'
  } finally {
    saving.value = false
  }
}

const deleteUser = async (user) => {
  if (!confirm(`Are you sure you want to delete user "${user.name}"?`)) return
  try {
    await api.delete(`/admin/users/${user.id}`)
    successMsg.value = 'User deleted successfully.'
    fetchUsers(meta.value.current_page)
    setTimeout(() => { successMsg.value = '' }, 3000)
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete user.')
  }
}

onMounted(() => {
  fetchUsers()
  fetchRoles()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="font-serif text-2xl font-light tracking-tight text-neutral-900">User Management</h1>
        <p class="text-xs text-neutral-500 mt-1 uppercase tracking-widest font-mono">
          Manage system administrators, staff members, and store customers
        </p>
      </div>
      <button 
        @click="openCreateModal"
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-neutral-900 text-white text-xs font-medium uppercase tracking-widest hover:bg-neutral-800 transition"
      >
        <Plus class="w-4 h-4" />
        Add New User
      </button>
    </div>

    <!-- Alert Notifications -->
    <div v-if="successMsg" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
      <Check class="w-4 h-4 text-emerald-600" />
      {{ successMsg }}
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 border border-neutral-200 flex flex-col sm:flex-row gap-3">
      <div class="relative flex-1">
        <Search class="w-4 h-4 text-neutral-400 absolute left-3 top-1/2 -translate-y-1/2" />
        <input 
          v-model="search"
          @keyup.enter="fetchUsers(1)"
          type="text" 
          placeholder="Search by name, email, or phone..."
          class="w-full pl-9 pr-3 py-2 border border-neutral-200 text-xs text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-neutral-900"
        />
      </div>
      <select 
        v-model="selectedRole" 
        @change="fetchUsers(1)"
        class="border border-neutral-200 px-3 py-2 text-xs text-neutral-800 bg-white focus:outline-none focus:border-neutral-900"
      >
        <option value="">All Account Types</option>
        <option value="admin">Administrators</option>
        <option value="manager">Staff Managers</option>
        <option value="customer">Customers</option>
      </select>
      <button 
        @click="fetchUsers(1)"
        class="px-4 py-2 bg-neutral-100 hover:bg-neutral-200 text-neutral-800 text-xs font-mono uppercase transition"
      >
        Search
      </button>
    </div>

    <!-- Users Table -->
    <div class="bg-white border border-neutral-200 overflow-hidden">
      <div v-if="loading" class="py-16 text-center">
        <Loader2 class="w-6 h-6 animate-spin mx-auto text-neutral-400 mb-2" />
        <p class="text-xs font-mono text-neutral-500 uppercase tracking-widest">Loading Users...</p>
      </div>

      <div v-else-if="users.length === 0" class="py-16 text-center">
        <Shield class="w-8 h-8 text-neutral-300 mx-auto mb-2" />
        <p class="text-sm font-serif text-neutral-700">No users found</p>
        <p class="text-xs text-neutral-400 mt-1">Try adjusting your search criteria or add a new user.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-neutral-200 bg-neutral-50 text-[10px] font-mono uppercase tracking-widest text-neutral-500">
              <th class="py-3 px-4">User</th>
              <th class="py-3 px-4">Contact</th>
              <th class="py-3 px-4">Role</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4">Created</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-100 text-xs">
            <tr v-for="user in users" :key="user.id" class="hover:bg-neutral-50/70 transition">
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-neutral-200 text-neutral-700 font-mono text-xs flex items-center justify-center font-bold">
                    {{ user.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <p class="font-medium text-neutral-900">{{ user.name }}</p>
                    <p class="text-neutral-500 text-[11px] font-mono">{{ user.email }}</p>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4 text-neutral-600 font-mono text-[11px]">
                {{ user.phone || '—' }}
              </td>
              <td class="py-3 px-4">
                <span 
                  class="inline-flex items-center px-2 py-0.5 text-[10px] font-mono uppercase tracking-wider rounded"
                  :class="{
                    'bg-neutral-900 text-white': user.role === 'admin',
                    'bg-amber-100 text-amber-800': user.role === 'manager',
                    'bg-neutral-100 text-neutral-700': user.role === 'customer'
                  }"
                >
                  {{ user.role }}
                </span>
              </td>
              <td class="py-3 px-4">
                <span 
                  class="inline-flex items-center gap-1 text-[11px] font-mono uppercase"
                  :class="user.is_active ? 'text-emerald-600' : 'text-neutral-400'"
                >
                  <span class="w-1.5 h-1.5 rounded-full" :class="user.is_active ? 'bg-emerald-600' : 'bg-neutral-400'"></span>
                  {{ user.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="py-3 px-4 text-neutral-500 font-mono text-[11px]">
                {{ new Date(user.created_at).toLocaleDateString() }}
              </td>
              <td class="py-3 px-4 text-right">
                <div class="inline-flex items-center gap-2">
                  <button 
                    @click="openEditModal(user)"
                    class="p-1.5 text-neutral-500 hover:text-neutral-900 hover:bg-neutral-100 rounded transition"
                    title="Edit User"
                  >
                    <Edit2 class="w-3.5 h-3.5" />
                  </button>
                  <button 
                    @click="deleteUser(user)"
                    class="p-1.5 text-neutral-400 hover:text-rose-600 hover:bg-rose-50 rounded transition"
                    title="Delete User"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="meta.total > meta.per_page" class="p-4 border-t border-neutral-200 flex items-center justify-between text-xs font-mono text-neutral-500">
        <div>Showing {{ (meta.current_page - 1) * meta.per_page + 1 }} to {{ Math.min(meta.current_page * meta.per_page, meta.total) }} of {{ meta.total }} users</div>
        <div class="flex gap-2">
          <button 
            :disabled="meta.current_page <= 1"
            @click="fetchUsers(meta.current_page - 1)"
            class="px-3 py-1 border border-neutral-200 hover:bg-neutral-50 disabled:opacity-40"
          >
            Prev
          </button>
          <button 
            :disabled="meta.current_page >= meta.last_page"
            @click="fetchUsers(meta.current_page + 1)"
            class="px-3 py-1 border border-neutral-200 hover:bg-neutral-50 disabled:opacity-40"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Create / Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
      <div class="bg-white w-full max-w-lg border border-neutral-200 shadow-xl overflow-hidden">
        <div class="p-4 border-b border-neutral-200 flex items-center justify-between">
          <h2 class="font-serif text-lg text-neutral-900">
            {{ editingUser ? 'Edit User: ' + editingUser.name : 'Create New User' }}
          </h2>
          <button @click="showModal = false" class="text-neutral-400 hover:text-neutral-700">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="saveUser" class="p-6 space-y-4">
          <div v-if="errorMsg" class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
            <AlertCircle class="w-4 h-4 text-rose-500 shrink-0" />
            <span>{{ errorMsg }}</span>
          </div>

          <div>
            <label class="block text-xs font-mono uppercase tracking-wider text-neutral-600 mb-1">Full Name *</label>
            <input 
              v-model="form.name"
              type="text" 
              required
              class="w-full px-3 py-2 border border-neutral-200 text-xs focus:outline-none focus:border-neutral-900"
            />
          </div>

          <div>
            <label class="block text-xs font-mono uppercase tracking-wider text-neutral-600 mb-1">Email Address *</label>
            <input 
              v-model="form.email"
              type="email" 
              required
              class="w-full px-3 py-2 border border-neutral-200 text-xs focus:outline-none focus:border-neutral-900"
            />
          </div>

          <div>
            <label class="block text-xs font-mono uppercase tracking-wider text-neutral-600 mb-1">
              Password {{ editingUser ? '(Leave blank to keep current password)' : '*' }}
            </label>
            <input 
              v-model="form.password"
              type="password" 
              :required="!editingUser"
              placeholder="••••••••"
              class="w-full px-3 py-2 border border-neutral-200 text-xs focus:outline-none focus:border-neutral-900"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-mono uppercase tracking-wider text-neutral-600 mb-1">Phone</label>
              <input 
                v-model="form.phone"
                type="text" 
                placeholder="+1 555 0192"
                class="w-full px-3 py-2 border border-neutral-200 text-xs focus:outline-none focus:border-neutral-900"
              />
            </div>
            <div>
              <label class="block text-xs font-mono uppercase tracking-wider text-neutral-600 mb-1">Account Role *</label>
              <select 
                v-model="form.role"
                required
                class="w-full px-3 py-2 border border-neutral-200 text-xs bg-white focus:outline-none focus:border-neutral-900"
              >
                <option value="customer">Customer</option>
                <option value="manager">Staff Manager</option>
                <option value="admin">Administrator</option>
              </select>
            </div>
          </div>

          <!-- RBAC Roles assignment -->
          <div v-if="roles.length > 0">
            <label class="block text-xs font-mono uppercase tracking-wider text-neutral-600 mb-1">Assigned RBAC Roles</label>
            <div class="space-y-1.5 border border-neutral-200 p-3 max-h-36 overflow-y-auto">
              <label v-for="r in roles" :key="r.id" class="flex items-center gap-2 text-xs text-neutral-700 cursor-pointer">
                <input 
                  type="checkbox" 
                  :value="r.id" 
                  v-model="form.role_ids" 
                  class="rounded text-neutral-900 focus:ring-neutral-900"
                />
                <span>{{ r.name }}</span>
                <span class="text-neutral-400 font-mono text-[10px]">({{ r.slug }})</span>
              </label>
            </div>
          </div>

          <div class="pt-2">
            <label class="flex items-center gap-2 cursor-pointer">
              <input 
                type="checkbox" 
                v-model="form.is_active" 
                class="rounded text-neutral-900 focus:ring-neutral-900"
              />
              <span class="text-xs text-neutral-700">Account is active and allowed to login</span>
            </label>
          </div>

          <div class="pt-4 border-t border-neutral-200 flex justify-end gap-3">
            <button 
              type="button" 
              @click="showModal = false"
              class="px-4 py-2 border border-neutral-200 text-xs uppercase tracking-wider text-neutral-700 hover:bg-neutral-50"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="saving"
              class="px-5 py-2 bg-neutral-900 text-white text-xs uppercase tracking-widest hover:bg-neutral-800 disabled:opacity-50 inline-flex items-center gap-2"
            >
              <Loader2 v-if="saving" class="w-3.5 h-3.5 animate-spin" />
              {{ editingUser ? 'Update User' : 'Create User' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
