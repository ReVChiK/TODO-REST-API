# Примеры запросов (Postman/cURL)

## GET все задачи:
GET http://localhost/todo-api/tasks
```
Ответ: 
[{"id":1,"title":"Купить молоко","status":"pending",...}]
```
## GET задача по ID:
GET http://localhost/todo-api/tasks/1
## Создать задачу:
POST http://localhost/todo-api/tasks
```
Content-Type: application/json

{
    "title": "Купить молоко",
    "description": "2 литра",
    "status": "pending"
}
```
## Обновить задачу:
PUT http://localhost/todo-api/tasks/1
```
Content-Type: application/json

{
    "title": "Купить молоко и хлеб",
    "status": "completed"
}
```
## Удалить задачу:
DELETE http://localhost/todo-api/tasks/1
