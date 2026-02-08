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

## AI Question Generation
The `AiQuestionGenerator` service accepts a contestant profile and returns a structured payload containing the prompt used, question metadata, and an answer key. This is intentionally provider-agnostic so you can swap in OpenAI, local LLMs, or a curated dataset.

### Example Output
```json
{
  "generated_at": "2024-01-01T12:00:00Z",
  "contestant_profile": {
    "category": "bible_quiz",
    "age_group": "9-12",
    "difficulty": 2
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

## Adaptive Learning
`AdaptiveLearningService` ingests response history to identify weak topics, suggest drills, and recommend difficulty changes.

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

## API Endpoints
See `routes/api.php` for the proposed API map:
- Fetch questions by contestant
- Submit responses and auto-score
- Generate adaptive drills
- Analytics dashboard for teachers/directors

## Analytics
Analytics responses include leaderboards, weak topic summaries, and recent question set usage to help teachers and directors monitor progress.
