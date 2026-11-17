<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'
import { 
  Shield, Plus, Edit2, Trash2, Check, AlertCircle, X, Loader2, KeyRound, CheckSquare, Square 
} from 'lucide-vue-next'

const roles = ref([])
const availablePermissions = ref({})
const loading = ref(false)
const saving = ref(false)
const showModal = ref(false)
const editingRole = ref(null)
const successMsg = ref('')
const errorMsg = ref('')

const form = ref({
  name: '',
  slug: '',
  description: '',
  permission_ids: []
})

const fetchRoles = async () => {
  loading.value = true
  try {
    const res = await api.get('/admin/roles')
    if (res.data.success) {
      roles.value = res.data.data
      availablePermissions.value = res.data.available_permissions || {}
    }
  } catch (err) {
    console.error('Failed to load roles:', err)
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  editingRole.value = null
  form.value = {
    name: '',
    slug: '',
    description: '',
    permission_ids: []
  }
  errorMsg.value = ''
  showModal.value = true
}

const openEditModal = (role) => {
  editingRole.value = role
  form.value = {
    name: role.name,
    slug: role.slug,
    description: role.description || '',
    permission_ids: role.permissions ? role.permissions.map(p => p.id) : []
  }
  errorMsg.value = ''
  showModal.value = true
}

const toggleModule = (modulePermissions) => {
  const ids = modulePermissions.map(p => p.id)
  const allSelected = ids.every(id => form.value.permission_ids.includes(id))

  if (allSelected) {
    // Unselect all
    form.value.permission_ids = form.value.permission_ids.filter(id => !ids.includes(id))
  } else {
    // Select all
    const newIds = new Set([...form.value.permission_ids, ...ids])
    form.value.permission_ids = Array.from(newIds)
  }
}

const isModuleAllSelected = (modulePermissions) => {
  const ids = modulePermissions.map(p => p.id)
  return ids.length > 0 && ids.every(id => form.value.permission_ids.includes(id))
}

const saveRole = async () => {
  saving.value = true
  errorMsg.value = ''
  try {
    if (editingRole.value) {
      await api.put(`/admin/roles/${editingRole.value.id}`, form.value)
      successMsg.value = 'Role updated successfully.'
    } else {
      await api.post('/admin/roles', form.value)
      successMsg.value = 'Role created successfully.'
    }
    showModal.value = false
    fetchRoles()
    setTimeout(() => { successMsg.value = '' }, 3000)
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to save role.'
  } finally {
    saving.value = false
  }
}

const deleteRole = async (role) => {
  if (role.slug === 'admin') {
    alert('Super Admin role cannot be deleted.')
    return
  }
  if (!confirm(`Are you sure you want to delete role "${role.name}"?`)) return

  try {
    await api.delete(`/admin/roles/${role.id}`)
    successMsg.value = 'Role deleted successfully.'
    fetchRoles()
    setTimeout(() => { successMsg.value = '' }, 3000)
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete role.')
  }
}

onMounted(() => {
  fetchRoles()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="font-serif text-2xl font-light tracking-tight text-neutral-900">Roles & Permissions</h1>
        <p class="text-xs text-neutral-500 mt-1 uppercase tracking-widest font-mono">
          Granular Role-Based Access Control (RBAC) and Security Profiles
        </p>
      </div>
      <button 
        @click="openCreateModal"
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-neutral-900 text-white text-xs font-medium uppercase tracking-widest hover:bg-neutral-800 transition"
      >
        <Plus class="w-4 h-4" />
        Create New Role
      </button>
    </div>

    <!-- Alert Notifications -->
    <div v-if="successMsg" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
      <Check class="w-4 h-4 text-emerald-600" />
      {{ successMsg }}
    </div>

    <!-- Roles Grid / List -->
    <div v-if="loading" class="py-16 text-center bg-white border border-neutral-200">
      <Loader2 class="w-6 h-6 animate-spin mx-auto text-neutral-400 mb-2" />
      <p class="text-xs font-mono text-neutral-500 uppercase tracking-widest">Loading Roles & Permissions...</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="role in roles" 
        :key="role.id" 
        class="bg-white border border-neutral-200 flex flex-col justify-between hover:border-neutral-400 transition"
      >
        <div class="p-5">
          <div class="flex items-start justify-between gap-3 mb-3">
            <div>
              <div class="flex items-center gap-2">
                <Shield class="w-4 h-4 text-neutral-700" />
                <h3 class="font-serif text-lg font-medium text-neutral-900">{{ role.name }}</h3>
              </div>
              <p class="text-[11px] font-mono text-neutral-400 uppercase mt-0.5">slug: {{ role.slug }}</p>
            </div>
            <span 
              v-if="role.slug === 'admin'"
              class="px-2 py-0.5 text-[9px] font-mono uppercase tracking-widest bg-neutral-900 text-white rounded"
            >
              System Super
            </span>
          </div>

          <p class="text-xs text-neutral-600 mb-4 min-h-[32px] line-clamp-2">
            {{ role.description || 'No specific description provided for this security role.' }}
          </p>

          <!-- Permissions pill summary -->
          <div>
            <div class="text-[10px] font-mono uppercase tracking-widest text-neutral-400 mb-2 flex items-center justify-between">
              <span>Assigned Capabilities</span>
              <span>{{ role.permissions ? role.permissions.length : 0 }} grants</span>
            </div>
            <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto pr-1">
              <span 
                v-for="perm in (role.permissions || []).slice(0, 10)" 
                :key="perm.id"
                class="px-1.5 py-0.5 bg-neutral-100 text-neutral-700 text-[10px] font-mono rounded"
              >
                {{ perm.slug || perm.name }}
              </span>
              <span 
                v-if="role.permissions && role.permissions.length > 10"
                class="px-1.5 py-0.5 bg-neutral-200 text-neutral-600 text-[10px] font-mono rounded"
              >
                +{{ role.permissions.length - 10 }} more
              </span>
            </div>
          </div>
        </div>

        <div class="p-3 bg-neutral-50 border-t border-neutral-200 flex items-center justify-between">
          <span class="text-[11px] font-mono text-neutral-500">
            ID: #{{ role.id }}
          </span>
          <div class="inline-flex items-center gap-2">
            <button 
              @click="openEditModal(role)"
              class="px-2.5 py-1 text-xs border border-neutral-200 bg-white hover:bg-neutral-100 text-neutral-800 transition inline-flex items-center gap-1 font-mono uppercase"
            >
              <Edit2 class="w-3 h-3" />
              Edit
            </button>
            <button 
              v-if="role.slug !== 'admin'"
              @click="deleteRole(role)"
              class="px-2 py-1 text-xs text-rose-600 hover:bg-rose-50 transition font-mono uppercase"
              title="Delete Role"
            >
              <Trash2 class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create / Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
      <div class="bg-white w-full max-w-2xl border border-neutral-200 shadow-xl max-h-[90vh] flex flex-col overflow-hidden">
        <div class="p-4 border-b border-neutral-200 flex items-center justify-between shrink-0">
          <div class="flex items-center gap-2">
            <KeyRound class="w-4 h-4 text-neutral-800" />
            <h2 class="font-serif text-lg text-neutral-900">
              {{ editingRole ? 'Edit Role: ' + editingRole.name : 'Create Security Role' }}
            </h2>
          </div>
          <button @click="showModal = false" class="text-neutral-400 hover:text-neutral-700">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="saveRole" class="p-6 space-y-5 overflow-y-auto">
          <div v-if="errorMsg" class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
            <AlertCircle class="w-4 h-4 text-rose-500 shrink-0" />
            <span>{{ errorMsg }}</span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-mono uppercase tracking-wider text-neutral-600 mb-1">Role Title *</label>
              <input 
                v-model="form.name"
                type="text" 
                required
                placeholder="e.g. Inventory Supervisor"
                class="w-full px-3 py-2 border border-neutral-200 text-xs focus:outline-none focus:border-neutral-900"
              />
            </div>
            <div>
              <label class="block text-xs font-mono uppercase tracking-wider text-neutral-600 mb-1">Slug Identifier</label>
              <input 
                v-model="form.slug"
                type="text" 
                :disabled="editingRole && editingRole.slug === 'admin'"
                placeholder="e.g. inventory-supervisor"
                class="w-full px-3 py-2 border border-neutral-200 text-xs focus:outline-none focus:border-neutral-900 disabled:bg-neutral-100"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-mono uppercase tracking-wider text-neutral-600 mb-1">Role Scope / Description</label>
            <textarea 
              v-model="form.description"
              rows="2"
              placeholder="Describe what members of this role are permitted to oversee..."
              class="w-full px-3 py-2 border border-neutral-200 text-xs focus:outline-none focus:border-neutral-900"
            ></textarea>
          </div>

          <!-- Permission Matrix by Module -->
          <div class="pt-2">
            <div class="flex items-center justify-between mb-3 border-b border-neutral-200 pb-2">
              <h3 class="text-xs font-mono uppercase tracking-widest text-neutral-900 font-bold">Permissions Matrix</h3>
              <span class="text-[11px] font-mono text-neutral-500">{{ form.permission_ids.length }} selected</span>
            </div>

            <div class="space-y-4">
              <div 
                v-for="(perms, moduleName) in availablePermissions" 
                :key="moduleName"
                class="border border-neutral-200 p-3 bg-neutral-50/50"
              >
                <div class="flex items-center justify-between mb-2">
                  <span class="text-xs font-mono uppercase tracking-wider font-semibold text-neutral-800">
                    {{ moduleName }}
                  </span>
                  <button 
                    type="button" 
                    @click="toggleModule(perms)"
                    class="text-[10px] font-mono uppercase tracking-wider text-neutral-600 hover:text-neutral-900 underline"
                  >
                    {{ isModuleAllSelected(perms) ? 'Deselect All' : 'Select All' }}
                  </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                  <label 
                    v-for="perm in perms" 
                    :key="perm.id" 
                    class="flex items-start gap-2 text-xs text-neutral-700 cursor-pointer bg-white p-2 border border-neutral-100 hover:border-neutral-300 transition"
                  >
                    <input 
                      type="checkbox" 
                      :value="perm.id" 
                      v-model="form.permission_ids"
                      class="rounded text-neutral-900 focus:ring-neutral-900 mt-0.5"
                    />
                    <div>
                      <p class="font-medium text-neutral-900">{{ perm.name }}</p>
                      <p class="text-[10px] font-mono text-neutral-400">{{ perm.slug }}</p>
                    </div>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="pt-4 border-t border-neutral-200 flex justify-end gap-3 sticky bottom-0 bg-white">
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
              {{ editingRole ? 'Update Permissions' : 'Save Role' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
