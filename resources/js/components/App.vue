<template>
    <div class="container mt-5">
      <h1 class="mb-4">Employee Management</h1>

      <!-- Form to Create or Edit Employees -->
      <div class="card mb-4">
        <div class="card-body">
          <h5 class="card-title">{{ isEdit ? 'Edit Employee' : 'Create Employee' }}</h5>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Name</label>
              <input
                type="text"
                v-model="form.name"
                class="form-control"
                :class="{ 'is-invalid': errors.name }"
                placeholder="Enter name"
              />
              <div v-if="errors.name" class="invalid-feedback">{{ errors.name }}</div>
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label">Email</label>
              <input
                type="email"
                v-model="form.email"
                class="form-control"
                :class="{ 'is-invalid': errors.email }"
                placeholder="Enter email"
              />
              <div v-if="errors.email" class="invalid-feedback">{{ errors.email }}</div>
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label">Department</label>
              <select
                v-model="form.department_id"
                class="form-select"
                :class="{ 'is-invalid': errors.department_id }"
              >
                <option value="" disabled>Select department</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                  {{ dept.name }}
                </option>
              </select>
              <div v-if="errors.department_id" class="invalid-feedback">{{ errors.department_id }}</div>
            </div>
          </div>

          <button @click="submitEmployee" class="btn btn-primary">
            {{ isEdit ? 'Update' : 'Create' }}
          </button>
          <button v-if="isEdit" @click="resetForm" class="btn btn-secondary ms-2">
            Cancel
          </button>
        </div>
      </div>

      <!-- Employees List -->
      <div v-if="employees.length" class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Name</th><th>Email</th><th>Department</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="emp in employees" :key="emp.id">
              <td>{{ emp.name }}</td>
              <td>{{ emp.email }}</td>
              <td>{{ emp.department.name }}</td>
              <td>
                <button class="btn btn-sm btn-warning me-2" @click="editEmployee(emp)">
                  Edit
                </button>
                <button class="btn btn-sm btn-danger" @click="deleteEmployee(emp.id)">
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="alert alert-warning">No employees found. Please create one.</div>
    </div>
  </template>

  <script>
  import axios from 'axios'

  export default {
    name: 'App',
    data() {
      return {
        employees: [],
        departments: [],
        form: {
          name: '',
          email: '',
          department_id: ''
        },
        isEdit: false,
        editId: null,
        errors: {}
      }
    },
    mounted() {
      this.getDepartments()
      this.getEmployees()
    },
    methods: {
      // Fetch departments for dropdown
      async getDepartments() {
        try {
          const res = await axios.get('/api/departments')
          this.departments = res.data.data || res.data
        } catch (e) {
          console.error(e)
        }
      },

      // Fetch all employees
      async getEmployees() {
        try {
          const res = await axios.get('/api/employees')
          this.employees = res.data.data || res.data
        } catch (e) {
          console.error(e)
        }
      },

      // Validate form fields
      validateForm() {
        this.errors = {}
        if (!this.form.name) {
          this.errors.name = 'The name field is required.'
        }
        if (!this.form.email) {
          this.errors.email = 'The email field is required.'
        }
        if (!this.form.department_id) {
          this.errors.department_id = 'Please select a department.'
        }
        return Object.keys(this.errors).length === 0
      },

      // Create or update employee
      async submitEmployee() {
        if (!this.validateForm()) return

        try {
          if (this.isEdit) {
            await axios.put(`/api/employees/${this.editId}`, this.form)
          } else {
            await axios.post('/api/employees', this.form)
          }
          this.getEmployees()
          this.resetForm()
        } catch (e) {
          // show server-side validation errors
          if (e.response?.data?.errors) {
            this.errors = { ...e.response.data.errors }
          } else {
            console.error(e)
          }
        }
      },

      // Populate form for editing
      editEmployee(emp) {
        this.isEdit = true
        this.editId = emp.id
        this.form.name = emp.name
        this.form.email = emp.email
        this.form.department_id = emp.department.id
        this.errors = {}
      },

      // Delete employee
      async deleteEmployee(id) {
        if (!confirm('Delete this employee?')) return
        try {
          await axios.delete(`/api/employees/${id}`)
          this.getEmployees()
        } catch (e) {
          console.error(e)
        }
      },

      // Reset form back to create-mode
      resetForm() {
        this.form = { name: '', email: '', department_id: '' }
        this.isEdit = false
        this.editId = null
        this.errors = {}
      }
    }
  }
  </script>

  <style scoped>
  .container { max-width: 900px; }
  </style>
