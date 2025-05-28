from flask import Flask, request, jsonify
from flask_sqlalchemy import SQLAlchemy
from flask_cors import CORS
from werkzeug.security import generate_password_hash, check_password_hash
from datetime import datetime
import jwt
import os
from functools import wraps
import random

app = Flask(__name__)
app.config['SECRET_KEY'] = 'votre-clé-secrète-très-sécurisée'
app.config['SQLALCHEMY_DATABASE_URI'] = 'mysql+pymysql://root:root@localhost/clusterproject'
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

db = SQLAlchemy(app)
CORS(app)

# Modèles de base de données
class User(db.Model):
    __tablename__ = 'user'
    id_user = db.Column(db.Integer, primary_key=True)
    lastname = db.Column(db.String(100), nullable=False)
    firstname = db.Column(db.String(100), nullable=False)
    password = db.Column(db.String(255), nullable=False)
    class_name = db.Column('class', db.String(50))
    role = db.Column(db.String(50))
    email = db.Column(db.String(150), unique=True, nullable=False)

class Demand(db.Model):
    __tablename__ = 'demand'
    id_demand = db.Column(db.Integer, primary_key=True)
    id_user = db.Column(db.Integer, db.ForeignKey('user.id_user'))
    date_start = db.Column(db.DateTime)
    date_finish = db.Column(db.DateTime)
    ispublic = db.Column(db.Boolean, default=False)
    group_size = db.Column(db.Integer)
    vote_size = db.Column(db.Integer)
    istreated = db.Column(db.Boolean, default=False)
    repartition_score = db.Column(db.Float)

class AnswerStudent(db.Model):
    __tablename__ = 'answer_student'
    id_answer = db.Column(db.Integer, primary_key=True)
    id_user = db.Column(db.Integer, db.ForeignKey('user.id_user'), nullable=False)
    id_demand = db.Column(db.Integer, db.ForeignKey('demand.id_demand'), nullable=False)
    ignore_student = db.Column(db.Boolean, default=False)

class UserAnswer(db.Model):
    __tablename__ = 'user_answer'
    id_user_answer = db.Column(db.Integer, primary_key=True)
    id_user = db.Column(db.Integer, db.ForeignKey('user.id_user'), nullable=False)
    id_user2 = db.Column(db.Integer, db.ForeignKey('user.id_user'), nullable=False)
    id_answer = db.Column(db.Integer, db.ForeignKey('answer_student.id_answer'), nullable=False)
    Affinity = db.Column(db.Integer)

class Group(db.Model):
    __tablename__ = 'group'
    id_group = db.Column(db.Integer, primary_key=True)
    id_demand = db.Column(db.Integer, db.ForeignKey('demand.id_demand'), nullable=False)
    group_name = db.Column(db.String(100))

class GroupUser(db.Model):
    __tablename__ = 'group_user'
    id_group_user = db.Column(db.Integer, primary_key=True)
    id_demand = db.Column(db.Integer, db.ForeignKey('demand.id_demand'), nullable=False)
    id_user = db.Column(db.Integer, db.ForeignKey('user.id_user'), nullable=False)

# Décorateur pour vérifier le token
def token_required(f):
    @wraps(f)
    def decorated(*args, **kwargs):
        token = request.headers.get('Authorization')
        if not token:
            return jsonify({'message': 'Token manquant'}), 401
        
        try:
            if token.startswith('Bearer '):
                token = token[7:]
            data = jwt.decode(token, app.config['SECRET_KEY'], algorithms=['HS256'])
            current_user = User.query.get(data['user_id'])
        except:
            return jsonify({'message': 'Token invalide'}), 401
        
        return f(current_user, *args, **kwargs)
    return decorated

# Routes d'authentification
@app.route('/api/register', methods=['POST'])
def register():
    data = request.get_json()
    
    if User.query.filter_by(email=data['email']).first():
        return jsonify({'message': 'Email déjà utilisé'}), 400
    
    hashed_password = generate_password_hash(data['password'])
    
    new_user = User(
        lastname=data['lastname'],
        firstname=data['firstname'],
        email=data['email'],
        password=hashed_password,
        class_name=data.get('class_name'),
        role=data['role']
    )
    
    db.session.add(new_user)
    db.session.commit()
    
    return jsonify({'message': 'Utilisateur créé avec succès'}), 201

@app.route('/api/login', methods=['POST'])
def login():
    data = request.get_json()
    user = User.query.filter_by(email=data['email']).first()
    
    if user and check_password_hash(user.password, data['password']):
        token = jwt.encode({
            'user_id': user.id_user,
            'role': user.role
        }, app.config['SECRET_KEY'], algorithm='HS256')
        
        return jsonify({
            'token': token,
            'user': {
                'id': user.id_user,
                'firstname': user.firstname,
                'lastname': user.lastname,
                'email': user.email,
                'role': user.role,
                'class': user.class_name
            }
        })
    
    return jsonify({'message': 'Identifiants incorrects'}), 401

# Routes pour les professeurs
@app.route('/api/demands', methods=['POST'])
@token_required
def create_demand(current_user):
    if current_user.role != 'teacher':
        return jsonify({'message': 'Accès non autorisé'}), 403
    
    data = request.get_json()
    students = data.get('students', [])  # Liste des IDs d'élèves sélectionnés
    
    new_demand = Demand(
        id_user=current_user.id_user,
        date_start=datetime.strptime(data['date_start'], '%Y-%m-%d %H:%M:%S'),
        date_finish=datetime.strptime(data['date_finish'], '%Y-%m-%d %H:%M:%S'),
        group_size=data['group_size'],
        vote_size=data['vote_size'],
        ispublic=False
    )
    
    db.session.add(new_demand)
    db.session.flush()  # Pour obtenir l'id_demand

    # Ajoute les élèves sélectionnés dans group_user
    for student_id in students:
        group_user = GroupUser(
            id_demand=new_demand.id_demand,
            id_user=student_id
        )
        db.session.add(group_user)

    db.session.commit()
    
    return jsonify({'message': 'Formulaire créé avec succès', 'demand_id': new_demand.id_demand}), 201

@app.route('/api/demands', methods=['GET'])
@token_required
def get_demands(current_user):
    if current_user.role != 'teacher':
        return jsonify({'message': 'Accès non autorisé'}), 403
    
    demands = Demand.query.filter_by(id_user=current_user.id_user).all()
    
    demands_list = []
    for demand in demands:
        demands_list.append({
            'id_demand': demand.id_demand,
            'date_start': demand.date_start.isoformat() if demand.date_start else None,
            'date_finish': demand.date_finish.isoformat() if demand.date_finish else None,
            'ispublic': demand.ispublic,
            'group_size': demand.group_size,
            'vote_size': demand.vote_size,
            'istreated': demand.istreated
        })
    
    return jsonify(demands_list)

@app.route('/api/demands/<int:demand_id>/publish', methods=['PUT'])
@token_required
def publish_demand(current_user, demand_id):
    if current_user.role != 'teacher':
        return jsonify({'message': 'Accès non autorisé'}), 403
    
    demand = Demand.query.filter_by(id_demand=demand_id, id_user=current_user.id_user).first()
    if not demand:
        return jsonify({'message': 'Formulaire non trouvé'}), 404
    
    demand.ispublic = True
    db.session.commit()
    
    return jsonify({'message': 'Formulaire publié avec succès'})

@app.route('/api/students', methods=['GET'])
@token_required
def get_students(current_user):
    if current_user.role != 'teacher':
        return jsonify({'message': 'Accès non autorisé'}), 403
    
    students = User.query.filter_by(role='student').all()
    
    students_list = []
    for student in students:
        students_list.append({
            'id_user': student.id_user,
            'firstname': student.firstname,
            'lastname': student.lastname,
            'email': student.email,
            'class': student.class_name
        })
    
    return jsonify(students_list)

# Routes pour les étudiants
@app.route('/api/public-demands', methods=['GET'])
@token_required
def get_public_demands(current_user):
    if current_user.role != 'student':
        return jsonify({'message': 'Accès non autorisé'}), 403
    
    demands = Demand.query.filter_by(ispublic=True).all()
    
    demands_list = []
    for demand in demands:
        # Vérifier si l'étudiant a déjà répondu
        has_answered = AnswerStudent.query.filter_by(
            id_user=current_user.id_user,
            id_demand=demand.id_demand
        ).first() is not None
        
        demands_list.append({
            'id_demand': demand.id_demand,
            'date_start': demand.date_start.isoformat() if demand.date_start else None,
            'date_finish': demand.date_finish.isoformat() if demand.date_finish else None,
            'vote_size': demand.vote_size,
            'has_answered': has_answered
        })
    
    return jsonify(demands_list)

@app.route('/api/demands/<int:demand_id>/answer', methods=['POST'])
@token_required
def submit_answer(current_user, demand_id):
    if current_user.role != 'student':
        return jsonify({'message': 'Accès non autorisé'}), 403
    
    data = request.get_json()
    
    # Vérifier si l'étudiant a déjà répondu
    existing_answer = AnswerStudent.query.filter_by(
        id_user=current_user.id_user,
        id_demand=demand_id
    ).first()
    
    if existing_answer:
        return jsonify({'message': 'Vous avez déjà répondu à ce formulaire'}), 400
    
    # Créer la réponse
    new_answer = AnswerStudent(
        id_user=current_user.id_user,
        id_demand=demand_id
    )
    db.session.add(new_answer)
    db.session.flush()  # Pour obtenir l'ID
    
    # Enregistrer les affinités
    for preference in data['preferences']:
        user_answer = UserAnswer(
            id_user=current_user.id_user,
            id_user2=preference['user_id'],
            id_answer=new_answer.id_answer,
            Affinity=preference['affinity']
        )
        db.session.add(user_answer)
    
    db.session.commit()
    
    return jsonify({'message': 'Réponse enregistrée avec succès'}), 201

# Route pour générer les groupes optimisés
@app.route('/api/demands/<int:demand_id>/generate-groups', methods=['POST'])
@token_required
def generate_groups(current_user, demand_id):
    if current_user.role != 'teacher':
        return jsonify({'message': 'Accès non autorisé'}), 403
    
    demand = Demand.query.filter_by(id_demand=demand_id, id_user=current_user.id_user).first()
    if not demand:
        return jsonify({'message': 'Formulaire non trouvé'}), 404
    
    # Récupérer tous les étudiants qui ont répondu
    answered_students = db.session.query(User).join(AnswerStudent).filter(
        AnswerStudent.id_demand == demand_id
    ).all()
    
    if not answered_students:
        return jsonify({'message': 'Aucune réponse disponible'}), 400
    
    # Algorithme simple de répartition (vous pouvez l'améliorer)
    students_list = list(answered_students)
    random.shuffle(students_list)
    
    groups = []
    group_size = demand.group_size
    
    for i in range(0, len(students_list), group_size):
        group_students = students_list[i:i+group_size]
        groups.append([{
            'id_user': student.id_user,
            'firstname': student.firstname,
            'lastname': student.lastname,
            'email': student.email
        } for student in group_students])
    
    # Marquer le formulaire comme traité
    demand.istreated = True
    db.session.commit()
    
    return jsonify({
        'groups': groups,
        'total_students': len(students_list),
        'total_groups': len(groups)
    })

if __name__ == '__main__':
    with app.app_context():
        db.create_all()
    app.run(debug=True)