class Review {
  const Review({
    required this.author,
    required this.rating,
    required this.comment,
    required this.time,
    this.avatarInitial = 'U',
  });

  final String author;
  final int rating;
  final String comment;
  final String time;
  final String avatarInitial;
}
