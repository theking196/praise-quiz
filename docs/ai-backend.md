# Praise Quiz AI Backend Engine

## Overview
This module provides the core data structures, AI question generation workflow, adaptive learning logic, and scoring rules for the competition prep platform. It is designed to run in a Laravel + Blade + Livewire stack while keeping the AI engine modular for future frontends.

## Database Schema
Use `database/schema.sql` to create the baseline tables:
- Users, age groups, categories, competitions
- Contestants and their historical competitions
- Questions, question sets, and question set items
- Contestant responses and performance analytics
- Question history to prevent repeats
- AI settings for configurable question mixing

Laravel migrations are provided in `database/migrations/` for a standard `php artisan migrate` workflow.

## AI Question Generation
The `AiQuestionGenerator` service accepts a contestant model and returns a structured payload containing the prompt used, question metadata, and an answer key. The generator can mix AI content with missed/correct history based on `ai_settings`.
`AiProviderService` is a placeholder for OpenAI or local LLM integration.

### Example Output
```json
{
  "generated_at": "2024-01-01T12:00:00Z",
  "contestant_profile": {
    "contestant_id": 42,
    "category": "bible_quiz",
    "age_group": "9-12",
    "difficulty": 2
  },
  "mix_config": {
    "mix_new_percentage": 50,
    "mix_missed_percentage": 30,
    "mix_old_percentage": 20
  },
  "questions": [
    {
      "id": "bible_quiz-1",
      "content": "Question 1: Who led Israel after Moses?",
      "type": "mcq",
      "options": ["Joshua", "David", "Solomon", "Samuel"],
      "correct_answer": "Joshua",
      "lesson_reference": "Luke 15:11-32",
      "prompt_used": "Create a bible_quiz question for age group 9-12, difficulty 2."
    }
  ]
}
```

## Adaptive Question Sets
`QuestionSetGenerator` builds a mixed set (AI + missed + correct) and stores it in `question_sets` and `question_set_items`, updating `question_history` to avoid repeats.

### Example Question Set Response
```json
{
  "contestant": {"id": 42, "difficulty_level": 2},
  "question_set": {"id": 15, "name": "Adaptive Set 2024-01-01 12:00:00"},
  "items": [
    {"question_id": 1001, "sequence_order": 1, "points": 10}
  ],
  "analysis": {
    "weak_topics": [{"topic": "prophets", "mistakes": 3}],
    "average_time": 14.2,
    "recommended_difficulty": 2,
    "drill_plan": [{"topic": "prophets", "target_questions": 6, "focus": "timed_practice"}],
    "badges": ["consistent_score"],
    "stage_progress": {"stage": "intermediate", "previous": "starter"},
    "learning_patterns": {"fast_responder": 3, "slow_responder": 1}
  },
  "mix_config": {"mix_new_percentage": 50, "mix_missed_percentage": 30, "mix_old_percentage": 20}
}
```

## Adaptive Learning
`AdaptiveLearningService` ingests response history to identify weak topics, suggest drills, recommend difficulty changes, and track stages/badges.

### Example Drill Plan
```json
{
  "weak_topics": [
    {"topic": "prophets", "mistakes": 3}
  ],
  "average_time": 14.2,
  "recommended_difficulty": 2,
  "drill_plan": [
    {"topic": "prophets", "target_questions": 6, "focus": "timed_practice"}
  ]
}
```

## Scoring
`ScoringService` supports difficulty-weighted CBT scoring and rubric-based essay scoring.

### Example CBT Score
```json
{
  "score": 15,
  "difficulty_multiplier": 1.5,
  "time_bonus": 3
}
```

### Example Essay Score
```json
{
  "score": 27,
  "breakdown": {
    "content": 10,
    "scripture_application": 10,
    "structure": 7
  }
}
```

## Analytics
Analytics responses include leaderboards, weak topic summaries, and recent question set usage to help teachers and directors monitor progress.

### Example Leaderboard
```json
{
  "leaderboard": [
    {"contestant_id": 42, "total_score": 275}
  ]
}
```

### Example Average Scores
```json
{
  "average_scores": [
    {"contestant_id": 42, "average_score": 88.5}
  ]
}
```

### Example Drill Recommendations
```json
{
  "drill_recommendations": [
    {
      "contestant_id": 42,
      "drills": [
        {"topic": "prophets", "target_questions": 6, "focus": "timed_practice"}
      ]
    }
  ]
}
```

### Example Weak Topic Heatmap
```json
{
  "weak_topics": [
    {"topic": "prophets", "mistakes": 3},
    {"topic": "parables", "mistakes": 2}
  ]
}
```

## API Endpoints
See `routes/api.php` for the proposed API map:
- Generate question sets per contestant
- Submit responses and auto-score
- Fetch AI-generated question sets via `/api/questions`
- Analytics leaderboards, weak topics, averages, drill recommendations, recent sets, export
- Admin endpoints for AI settings and question moderation

## Admin Controls
Admin APIs allow setting mix percentages and maximum difficulty by age group, along with approving AI-generated questions for production use.

### Example AI Settings Payload
```json
{
  "mix_new_percentage": 50,
  "mix_missed_percentage": 30,
  "mix_old_percentage": 20,
  "max_difficulty_by_age_group": {
    "1": 2,
    "2": 3
  }
}
```

## Contestant API Examples
### Fetch Questions (GET /api/questions)
```json
{
  "contestant_id": 42,
  "category_id": 2,
  "difficulty_level": 2,
  "number_of_questions": 10
}
```

### Submit Response
```json
{
  "question_id": 1001,
  "response": "Joshua",
  "is_correct": true,
  "time_taken": 12.5,
  "points": 10,
  "difficulty": 2,
  "question_type": "bible_quiz",
  "topic": "leadership"
}
```

### Submit Essay Response
```json
{
  "question_id": 2002,
  "response": "Essay response body",
  "is_correct": true,
  "time_taken": 180,
  "difficulty": 2,
  "question_type": "essay",
  "rubric": {
    "content": 10,
    "scripture_application": 8,
    "structure": 7
  }
}
```

### Fetch Analytics
```json
{
  "analytics": [
    {
      "contestant_id": 42,
      "total_score": 275,
      "weak_topics": [{"topic": "prophets", "mistakes": 3}]
    }
  ]
}
```

### Practice Drills
```json
{
  "contestant_id": 42,
  "drills": [
    {"topic": "prophets", "target_questions": 6, "focus": "timed_practice"}
  ]
}
```

## Teacher/Director API Examples
### Fetch Students
```json
{
  "students": [
    {
      "id": 42,
      "category_id": 2,
      "age_group_id": 1,
      "competition_id": 2024
    }
  ]
}
```

### Recent Responses
```json
{
  "responses": [
    {
      "contestant_id": 42,
      "question_id": 1001,
      "is_correct": true,
      "time_taken": 12.5
    }
  ]
}
```

## Authentication & Roles
Apply `auth` and `role` middleware on API routes to enforce access:
- Contestant routes: `role:contestant`
- Teacher/director routes: `role:teacher|director`
- Admin routes: `role:admin`

## HTTP Test Examples (cURL)
```bash
curl -G \"https://example.test/api/questions\" \\
  --data-urlencode \"contestant_id=42\" \\
  --data-urlencode \"category_id=2\" \\
  --data-urlencode \"difficulty_level=2\"

curl -X POST \"https://example.test/api/responses\" \\
  -H \"Content-Type: application/json\" \\
  -d '{\"contestant_id\":42,\"question_id\":1001,\"response\":\"Joshua\",\"is_correct\":true,\"time_taken\":12.5,\"difficulty\":2,\"question_type\":\"bible_quiz\"}'

curl -G \"https://example.test/api/performance\" \\
  --data-urlencode \"contestant_id=42\"

curl -G \"https://example.test/api/practice-drills\" \\
  --data-urlencode \"contestant_id=42\"
```

## Livewire Integration Notes
- Livewire contestant components can call `/api/questions` and `/api/responses` to drive practice sessions.
- Teacher dashboards can consume `/api/teacher/students` and `/api/teacher/question-sets` for monitoring.
