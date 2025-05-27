<template>
  <div>
    <!-- Auth -->
    <div v-if="!isAuthenticated" class="auth-container">
      <div class="auth-card card">
        <h2 style="text-align: center; margin-bottom: 30px; color: #333;">
          {{ isLoginMode ? 'Connexion' : 'Inscription' }}
        </h2>
        <div v-if="message" :class="'alert ' + (messageType === 'success' ? 'alert-success' : 'alert-danger')">
          {{ message }}
        </div>
        <form @submit.prevent="isLoginMode ? login() : register()">
          <div v-if="!isLoginMode" class="form-group">
            <label>Nom</label>
            <input type="text" v-model="authForm.lastname" class="form-control" required>
          </div>
          <div v-if="!isLoginMode" class="form-group">
            <label>Prénom</label>
            <input type="text" v-model="authForm.firstname" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" v-model="authForm.email" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" v-model="authForm.password" class="form-control" required>
          </div>
          <div v-if="!isLoginMode" class="form-group">
            <label>Classe (optionnel pour étudiants)</label>
            <input type="text" v-model="authForm.class_name" class="form-control">
          </div>
          <div v-if="!isLoginMode" class="form-group">
            <label>Rôle</label>
            <select v-model="authForm.role" class="form-control" required>
              <option value="">Sélectionner un rôle</option>
              <option value="student">Étudiant</option>
              <option value="teacher">Professeur</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 15px;">
            {{ isLoginMode ? 'Se connecter' : 'S\'inscrire' }}
          </button>
        </form>
        <p style="text-align: center;">
          {{ isLoginMode ? 'Pas de compte ?' : 'Déjà un compte ?' }}
          <a href="#" @click="isLoginMode = !isLoginMode" style="color: #667eea;">
            {{ isLoginMode ? 'S\'inscrire' : 'Se connecter' }}
          </a>
        </p>
      </div>
    </div>

    <!-- Dashboard -->
    <div v-else class="container">
      <!-- Header -->
      <div class="header">
        <div class="header-content">
          <h1>Gestionnaire de Groupes</h1>
          <div>
            <span>Bonjour {{ user.firstname }} {{ user.lastname }} ({{ user.role === 'teacher' ? 'Professeur' : 'Étudiant' }})</span>
            <button @click="logout" class="btn btn-secondary" style="margin-left: 15px;">Déconnexion</button>
          </div>
        </div>
      </div>

      <!-- Professeur -->
      <div v-if="user.role === 'teacher'">
        <div class="nav-tabs">
          <button @click="activeTab = 'create'" :class="'nav-tab ' + (activeTab === 'create' ? 'active' : '')">
            Créer un formulaire
          </button>
          <button @click="activeTab = 'history'" :class="'nav-tab ' + (activeTab === 'history' ? 'active' : '')">
            Historique
          </button>
        </div>
        <div v-if="activeTab === 'create'" class="card">
          <h3>Créer un nouveau formulaire</h3>
          <div v-if="message" :class="'alert ' + (messageType === 'success' ? 'alert-success' : 'alert-danger')">
            {{ message }}
          </div>
          <form @submit.prevent="createDemand()">
            <div class="form-group">
              <label>Date de début</label>
              <input type="datetime-local" v-model="demandForm.date_start" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Date de fin</label>
              <input type="datetime-local" v-model="demandForm.date_finish" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Nombre de personnes par groupe</label>
              <input type="number" v-model="demandForm.group_size" class="form-control" min="2" required>
            </div>
            <div class="form-group">
              <label>Nombre de préférences à sélectionner</label>
              <input type="number" v-model="demandForm.vote_size" class="form-control" min="1" max="10" required>
            </div>
            <div class="form-group">
              <label>Élèves concernés</label>
              <div v-if="studentsList.length === 0" style="color: #888;">Aucun élève disponible</div>
              <div v-for="student in studentsList" :key="student.id_user" style="margin-bottom: 5px;">
                <input type="checkbox" :value="student.id_user" v-model="demandForm.students" />
                {{ student.firstname }} {{ student.lastname }} ({{ student.email }})
              </div>
            </div>
            <button type="submit" class="btn btn-primary">Créer le formulaire</button>
          </form>
        </div>
        <div v-if="activeTab === 'history'" class="card">
          <h3>Historique des formulaires</h3>
          <div v-if="demands.length === 0" style="text-align: center; padding: 40px; color: #666;">
            Aucun formulaire créé pour le moment
          </div>
          <table v-else class="table">
            <thead>
              <tr>
                <th>Date de début</th>
                <th>Date de fin</th>
                <th>Taille groupe</th>
                <th>Votes requis</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="demand in demands" :key="demand.id_demand">
                <td>{{ formatDate(demand.date_start) }}</td>
                <td>{{ formatDate(demand.date_finish) }}</td>
                <td>{{ demand.group_size }}</td>
                <td>{{ demand.vote_size }}</td>
                <td>
                  <span v-if="demand.istreated" style="color: green;">✓ Traité</span>
                  <span v-else-if="demand.ispublic" style="color: orange;">🔓 Public</span>
                  <span v-else style="color: red;">🔒 Privé</span>
                </td>
                <td>
                  <button v-if="!demand.ispublic" @click="publishDemand(demand.id_demand)" class="btn btn-success" style="margin-right: 10px;">
                    Publier
                  </button>
                  <button v-if="demand.ispublic && !demand.istreated" @click="generateGroups(demand.id_demand)" class="btn btn-primary">
                    Générer groupes
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Étudiant -->
      <div v-else>
        <div class="card">
          <h3>Formulaires disponibles</h3>
          <div v-if="publicDemands.length === 0" style="text-align: center; padding: 40px; color: #666;">
            Aucun formulaire disponible pour le moment
          </div>
          <div v-for="demand in publicDemands" :key="demand.id_demand" class="group-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <div>
                <h4>Formulaire du {{ formatDate(demand.date_start) }}</h4>
                <p>Date limite: {{ formatDate(demand.date_finish) }}</p>
                <p>Nombre de préférences à sélectionner: {{ demand.vote_size }}</p>
              </div>
              <div>
                <button v-if="!demand.has_answered" @click="startAnswering(demand)" class="btn btn-primary">
                  Répondre
                </button>
                <span v-else style="color: green; font-weight: bold;">✓ Répondu</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Réponse -->
      <div v-if="showAnswerModal" class="modal" @click.self="closeAnswerModal">
        <div class="modal-content">
          <h3>Répondre au formulaire</h3>
          <p>Sélectionnez vos {{ currentDemand.vote_size }} préférences par ordre de priorité (1 = préféré, {{ currentDemand.vote_size }} = moins préféré)</p>
          <div v-if="message" :class="'alert ' + (messageType === 'success' ? 'alert-success' : 'alert-danger')">
            {{ message }}
          </div>
          <div v-for="student in availableStudents" :key="student.id_user" class="preference-item">
            <span>{{ student.firstname }} {{ student.lastname }}</span>
            <select v-model="preferences[student.id_user]" class="form-control" style="width: 100px;">
              <option value="">--</option>
              <option v-for="i in currentDemand.vote_size" :key="i" :value="i">{{ i }}</option>
            </select>
          </div>
          <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button @click="submitAnswer" class="btn btn-primary">Envoyer</button>
            <button @click="closeAnswerModal" class="btn btn-secondary">Annuler</button>
          </div>
        </div>
      </div>

      <!-- Modal Groupes -->
      <div v-if="showGroupsModal" class="modal" @click.self="closeGroupsModal">
        <div class="modal-content">
          <h3>Groupes générés</h3>
          <p>Total: {{ generatedGroups.total_students }} étudiants répartis en {{ generatedGroups.total_groups }} groupes</p>
          <div v-for="(group, index) in generatedGroups.groups" :key="index" class="group-card">
            <div class="group-title">Groupe {{ index + 1 }}</div>
            <ul class="student-list">
              <li v-for="student in group" :key="student.id_user">
                {{ student.firstname }} {{ student.lastname }} ({{ student.email }})
              </li>
            </ul>
          </div>
          <button @click="closeGroupsModal" class="btn btn-primary">Fermer</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'

const isAuthenticated = ref(false)
const isLoginMode = ref(true)
const user = ref<any>(null)
const message = ref('')
const messageType = ref('')
const activeTab = ref('create')

const authForm = reactive({
  email: '',
  password: '',
  firstname: '',
  lastname: '',
  class_name: '',
  role: ''
})

const demandForm = reactive({
  date_start: '',
  date_finish: '',
  group_size: 3,
  vote_size: 5,
  students: [] as number[]
})

const studentsList = ref<any[]>([])
const demands = ref<any[]>([])
const publicDemands = ref<any[]>([])
const availableStudents = ref<any[]>([])

const showAnswerModal = ref(false)
const currentDemand = ref<any>(null)
const preferences = reactive<{ [key: string]: any }>({})

const showGroupsModal = ref(false)
const generatedGroups = ref<any>(null)

function setDefaultDates() {
  const now = new Date()
  const tomorrow = new Date(now)
  tomorrow.setDate(now.getDate() + 1)
  demandForm.date_start = now.toISOString().slice(0, 16)
  demandForm.date_finish = tomorrow.toISOString().slice(0, 16)
}

function checkAuth() {
  const token = localStorage.getItem('token')
  const userData = localStorage.getItem('user')
  if (token && userData) {
    isAuthenticated.value = true
    user.value = JSON.parse(userData)
    setupAxiosHeaders()
    if (user.value.role === 'teacher') {
      loadDemands()
      loadStudents()
    } else {
      loadPublicDemands()
      loadAvailableStudents()
    }
  }
}

function setupAxiosHeaders() {
  const token = localStorage.getItem('token')
  if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
  }
}

async function login() {
  try {
    const response = await axios.post('http://localhost:5000/api/login', {
      email: authForm.email,
      password: authForm.password
    })
    localStorage.setItem('token', response.data.token)
    localStorage.setItem('user', JSON.stringify(response.data.user))
    isAuthenticated.value = true
    user.value = response.data.user
    setupAxiosHeaders()
    if (user.value.role === 'teacher') {
      loadDemands()
      loadStudents()
    } else {
      loadPublicDemands()
      loadAvailableStudents()
    }
    showMessage('Connexion réussie !', 'success')
  } catch (error: any) {
    showMessage(error.response?.data?.message || 'Erreur de connexion', 'error')
  }
}

async function register() {
  try {
    await axios.post('http://localhost:5000/api/register', authForm)
    showMessage('Inscription réussie ! Vous pouvez maintenant vous connecter.', 'success')
    isLoginMode.value = true
    resetAuthForm()
  } catch (error: any) {
    showMessage(error.response?.data?.message || 'Erreur d\'inscription', 'error')
  }
}

function logout() {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  isAuthenticated.value = false
  user.value = null
  delete axios.defaults.headers.common['Authorization']
}

async function createDemand() {
  try {
    await axios.post('http://localhost:5000/api/demands', {
      date_start: demandForm.date_start.replace('T', ' ') + ':00',
      date_finish: demandForm.date_finish.replace('T', ' ') + ':00',
      group_size: parseInt(demandForm.group_size as any),
      vote_size: parseInt(demandForm.vote_size as any),
      students: demandForm.students
    })
    showMessage('Formulaire créé avec succès !', 'success')
    loadDemands()
    resetDemandForm()
    demandForm.students = []
  } catch (error: any) {
    showMessage(error.response?.data?.message || 'Erreur lors de la création', 'error')
  }
}

async function loadDemands() {
  try {
    const response = await axios.get('http://localhost:5000/api/demands')
    demands.value = response.data
  } catch {
    showMessage('Erreur lors du chargement des formulaires', 'error')
  }
}

async function loadStudents() {
  try {
    const response = await axios.get('http://localhost:5000/api/students')
    studentsList.value = response.data
  } catch {
    studentsList.value = []
  }
}

async function publishDemand(demandId: number) {
  try {
    await axios.put(`http://localhost:5000/api/demands/${demandId}/publish`)
    showMessage('Formulaire publié avec succès !', 'success')
    loadDemands()
  } catch (error: any) {
    showMessage(error.response?.data?.message || 'Erreur lors de la publication', 'error')
  }
}

async function generateGroups(demandId: number) {
  try {
    const response = await axios.post(`http://localhost:5000/api/demands/${demandId}/generate-groups`)
    generatedGroups.value = response.data
    showGroupsModal.value = true
    loadDemands()
  } catch (error: any) {
    showMessage(error.response?.data?.message || 'Erreur lors de la génération des groupes', 'error')
  }
}

async function loadPublicDemands() {
  try {
    const response = await axios.get('http://localhost:5000/api/public-demands')
    publicDemands.value = response.data
  } catch {
    showMessage('Erreur lors du chargement des formulaires', 'error')
  }
}

async function loadAvailableStudents() {
  try {
    const response = await axios.get('http://localhost:5000/api/students')
    availableStudents.value = response.data.filter((student: any) => student.id_user !== user.value.id)
  } catch (error) {
    // ignore
  }
}

function startAnswering(demand: any) {
  currentDemand.value = demand
  Object.keys(preferences).forEach(key => delete preferences[key])
  showAnswerModal.value = true
}

async function submitAnswer() {
  const selectedPreferences = Object.values(preferences).filter(p => p !== '' && p !== null)
  if (selectedPreferences.length !== currentDemand.value.vote_size) {
    showMessage(`Vous devez sélectionner exactement ${currentDemand.value.vote_size} préférences`, 'error')
    return
  }
  const uniquePreferences = [...new Set(selectedPreferences)]
  if (uniquePreferences.length !== selectedPreferences.length) {
    showMessage('Vous ne pouvez pas donner la même priorité à plusieurs personnes', 'error')
    return
  }
  try {
    const preferencesArray = Object.entries(preferences)
      .filter(([userId, affinity]) => affinity !== '' && affinity !== null)
      .map(([userId, affinity]) => ({
        user_id: parseInt(userId),
        affinity: parseInt(affinity as string)
      }))
    await axios.post(`http://localhost:5000/api/demands/${currentDemand.value.id_demand}/answer`, {
      preferences: preferencesArray
    })
    showMessage('Réponse enregistrée avec succès !', 'success')
    closeAnswerModal()
    loadPublicDemands()
  } catch (error: any) {
    showMessage(error.response?.data?.message || 'Erreur lors de l\'envoi de la réponse', 'error')
  }
}

function closeAnswerModal() {
  showAnswerModal.value = false
  currentDemand.value = null
  Object.keys(preferences).forEach(key => delete preferences[key])
  message.value = ''
}

function closeGroupsModal() {
  showGroupsModal.value = false
  generatedGroups.value = null
}

function formatDate(dateString: string) {
  if (!dateString) return ''
  return new Date(dateString).toLocaleString('fr-FR')
}

function showMessage(text: string, type: string) {
  message.value = text
  messageType.value = type
  setTimeout(() => {
    message.value = ''
  }, 5000)
}

function resetAuthForm() {
  authForm.email = ''
  authForm.password = ''
  authForm.firstname = ''
  authForm.lastname = ''
  authForm.class_name = ''
  authForm.role = ''
}

function resetDemandForm() {
  setDefaultDates()
  demandForm.group_size = 3
  demandForm.vote_size = 5
}

onMounted(() => {
  setDefaultDates()
  checkAuth()
})
</script>

<style scoped>
.form-group {
  margin-bottom: 18px;
}
input[type="checkbox"] {
  margin-right: 8px;
}
.card {
  box-shadow: 0 2px 8px rgba(0,0,0,0.07);
  border-radius: 8px;
  padding: 24px;
  background: #fff;
  margin-bottom: 24px;
}
.btn {
  background: #667eea;
  color: #fff;
  border: none;
  border-radius: 4px;
  padding: 8px 18px;
  cursor: pointer;
  transition: background 0.2s;
}
.btn:hover {
  background: #5a67d8;
}
.alert-success {
  background: #e6fffa;
  color: #2c7a7b;
  border: 1px solid #b2f5ea;
  padding: 10px;
  border-radius: 4px;
  margin-bottom: 10px;
}
.alert-danger {
  background: #fff5f5;
  color: #c53030;
  border: 1px solid #fed7d7;
  padding: 10px;
  border-radius: 4px;
  margin-bottom: 10px;
}
</style>
