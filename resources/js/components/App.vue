<template>
    <div class="container mt-4">
        <h1 class="mb-4">Employee Admin</h1>

        <!-- Search & Filters -->
        <div class="row mb-3 g-2">
            <div class="col-md-4">
                <input v-model="filters.search" @input="fetch()" type="text" class="form-control"
                    placeholder="Search name or email" />
            </div>
            <div class="col-md-3">
                <select v-model="filters.department_id" @change="fetch()" class="form-select">
                    <option value="">All Departments</option>
                    <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <input v-model.number="filters.salary_min" @input="fetch()" type="number" class="form-control"
                    placeholder="Min Salary" />
            </div>
            <div class="col-md-2">
                <input v-model.number="filters.salary_max" @input="fetch()" type="number" class="form-control"
                    placeholder="Max Salary" />
            </div>
            <div class="col-md-1">
                <select v-model="filters.order" @change="fetch()" class="form-select">
                    <option value="desc">⇩ Date</option>
                    <option value="asc">⇧ Date</option>
                </select>
            </div>
        </div>

        <!-- Form -->
        <div class="card mb-4">
            <div class="card-body">
                <h5>{{ isEdit ? 'Edit' : 'Create' }} Employee</h5>
                <div class="row g-2">
                    <!-- Name -->
                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input v-model="form.name" type="text" class="form-control" :class="{ 'is-invalid': errors.name }" />
                        <div v-if="errors.name" class="invalid-feedback">{{ errors.name }}</div>
                    </div>

                    <!-- Email -->
                    <div class="col-md-3">
                        <label class="form-label">Email</label>
                        <input v-model="form.email" type="email" class="form-control" :class="{ 'is-invalid': errors.email }" />
                        <div v-if="errors.email" class="invalid-feedback">{{ errors.email }}</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Address</label>
                        <input v-model="form.address" type="text" class="form-control" :class="{ 'is-invalid': errors.address }" />
                        <div v-if="errors.email" class="invalid-feedback">{{ errors.address }}</div>
                    </div>

                    <!-- Department -->
                    <div class="col-md-3">
                        <label class="form-label">Department</label>
                        <select v-model="form.department_id" class="form-select" :class="{ 'is-invalid': errors.department_id }">
                            <option value="">Select Department</option>
                            <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                        </select>
                        <div v-if="errors.department_id" class="invalid-feedback">{{ errors.department_id }}</div>
                    </div>

                    <!-- Designation -->
                    <div class="col-md-3">
                        <label class="form-label">Designation</label>
                        <input v-model="form.designation" type="text" class="form-control" :class="{ 'is-invalid': errors.designation }" />
                        <div v-if="errors.designation" class="invalid-feedback">{{ errors.designation }}</div>
                    </div>

                    <!-- Salary -->
                    <div class="col-md-3">
                        <label class="form-label">Salary</label>
                        <input v-model.number="form.salary" type="number" class="form-control" :class="{ 'is-invalid': errors.salary }" />
                        <div v-if="errors.salary" class="invalid-feedback">{{ errors.salary }}</div>
                    </div>

                    <!-- Joining Date -->
                    <div class="col-md-3">
                        <label class="form-label">Joining Date</label>
                        <input v-model="form.joined_date" type="date" class="form-control" :class="{ 'is-invalid': errors.joined_date }" />
                        <div v-if="errors.joined_date" class="invalid-feedback">{{ errors.joined_date }}</div>
                    </div>
                </div>
                <button @click="submit()" class="btn btn-primary mt-2">
                    {{ isEdit ? 'Update' : 'Create' }}
                </button>
                <button v-if="isEdit" @click="reset()" class="btn btn-secondary mt-2 ms-2">
                    Cancel
                </button>
            </div>
        </div>

        <!-- Table -->
        <div v-if="list.length" class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Dept</th>
                        <th>Designation</th>
                        <th>Joined</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="e in list" :key="e.id">
                        <td>{{ e.name }}</td>
                        <td>{{ e.email }}</td>
                        <td>{{ e.department?.name }}</td>
                        <td>{{ e.details?.designation }}</td>
                        <td>{{ formatDate(e.details?.joined_date) }}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-light me-1" @click="startEdit(e)">
                                ✏️
                            </button>
                            <button class="btn btn-sm btn-light text-danger" @click="remove(e.id)">
                                🗑️
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-else class="alert alert-warning">No employees found.</div>

        <!-- Pagination -->
        <nav v-if="meta.last_page > 1" class="mt-3">
            <ul class="pagination justify-content-center">
                <li class="page-item" :class="{ disabled: meta.current_page === 1 }">
                    <a class="page-link" href="#" @click.prevent="fetch(meta.current_page - 1)">
                        &laquo;
                    </a>
                </li>

                <li class="page-item" :class="{ active: 1 === meta.current_page }">
                    <a class="page-link" href="#" @click.prevent="fetch(1)">1</a>
                </li>

                <li class="page-item disabled" v-if="meta.current_page > 4 && meta.last_page > 5">
                    <span class="page-link">...</span>
                </li>

                <template v-for="p in pageRange" :key="p">
                    <li class="page-item" :class="{ active: p === meta.current_page }">
                        <a class="page-link" href="#" @click.prevent="fetch(p)">{{ p }}</a>
                    </li>
                </template>

                <li class="page-item disabled" v-if="meta.current_page < (meta.last_page - 3) && meta.last_page > 5">
                    <span class="page-link">...</span>
                </li>

                <li class="page-item" v-if="meta.last_page !== 1"
                    :class="{ active: meta.last_page === meta.current_page }">
                    <a class="page-link" href="#" @click.prevent="fetch(meta.last_page)">
                        {{ meta.last_page }}
                    </a>
                </li>

                <li class="page-item" :class="{ disabled: meta.current_page === meta.last_page }">
                    <a class="page-link" href="#" @click.prevent="fetch(meta.current_page + 1)">
                        &raquo;
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
import axios from 'axios';

axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

export default {
    data() {
        return {
            list: [],
            meta: { current_page: 1, last_page: 1 },
            departments: [],
            filters: {
                search: '', department_id: '', salary_min: '', salary_max: '', order: 'desc',
            },
            form: {
                name: '',
                email: '',
                address: '',
                department_id: '',
                designation: '',
                salary: '',
                joined_date: ''
            },
            isEdit: false,
            editId: null,
            errors: {}
        }
    },
    computed: {
        pageRange() {
            const current = this.meta.current_page;
            const last = this.meta.last_page;
            const delta = 2;
            const range = [];

            for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
                range.push(i);
            }

            return range;
        }
    },
    mounted() {
        this.fetchDepartments();
        this.fetch();
    },
    methods: {
        async fetchDepartments() {
            try {
                const res = await axios.get('/api/departments');
                this.departments = res.data.data;
            } catch (error) {
                console.error('Department fetch error:', error);
            }
        },

        async fetch(page = 1) {
            try {
                const params = {
                    ...this.filters,
                    page,
                    per_page: 10,
                };
                const res = await axios.get('/api/employees', { params });
                this.list = res.data.data;
                this.meta = res.data.meta;
            } catch (error) {
                console.error('Employee fetch error:', error);
            }
        },

        validate() {
            this.errors = {};
            let isValid = true;

            // Required fields validation
            const requiredFields = [
                'name', 'email', 'department_id',
                'designation', 'salary', 'joined_date', 'address'
            ];

            requiredFields.forEach(field => {
                if (!this.form[field]) {
                    this.errors[field] = 'This field is required';
                    isValid = false;
                }
            });

            // Email format validation
            if (this.form.email && !this.validateEmail(this.form.email)) {
                this.errors.email = 'Invalid email format';
                isValid = false;
            }

            // Salary numeric validation
            if (this.form.salary && isNaN(this.form.salary)) {
                this.errors.salary = 'Must be a valid number';
                isValid = false;
            }

            return isValid;
        },

        validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        async submit() {
            if (!this.validate()) return;

            try {
                const payload = {
                    name: this.form.name,
                    email: this.form.email,
                    department_id: this.form.department_id,
                    details: {
                        designation: this.form.designation,
                        salary: this.form.salary,
                        joined_date: this.form.joined_date,
                        address: this.form.address
                    }
                };

                if (this.isEdit) {
                    await axios.put(`/api/employees/${this.editId}`, payload);
                } else {
                    await axios.post('/api/employees', payload);
                }

                this.fetch();
                this.reset();
            } catch (e) {
                console.error('Submission error:', e);
                if (e.response?.data?.errors) {
                    // Handle nested errors
                    this.errors = Object.entries(e.response.data.errors).reduce((acc, [key, value]) => {
                        acc[key.split('.').pop()] = value[0];
                        return acc;
                    }, {});
                }
            }
        },

        startEdit(employee) {
            this.isEdit = true;
            this.editId = employee.id;
            this.form = {
                name: employee.name,
                email: employee.email,
                department_id: employee.department?.id,
                designation: employee.details?.designation,
                address: employee.details?.address,
                salary: employee.details?.salary,
                joined_date: employee.details?.joined_date?.split(' ')[0] // Format date for input
            };
            this.errors = {};
        },

        async remove(id) {
            if (!confirm('Confirm delete?')) return;
            try {
                await axios.delete(`/api/employees/${id}`);
                this.fetch();
            } catch (error) {
                console.error('Delete error:', error);
            }
        },

        reset() {
            this.isEdit = false;
            this.editId = null;
            this.form = {
                name: '',
                email: '',
                department_id: '',
                designation: '',
                salary: '',
                joined_date: ''
            };
            this.errors = {};
        },

        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }
    }
}
</script>

<style scoped>
.container {
    max-width: 1200px;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

.pagination {
    flex-wrap: wrap;
    gap: 5px;
}

.page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.invalid-feedback {
    display: block;
}
</style>
