import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../app/theme.dart';

class StatusBadge extends StatelessWidget {
  final String status;
  final double fontSize;

  const StatusBadge({
    super.key,
    required this.status,
    this.fontSize = 11,
  });

  @override
  Widget build(BuildContext context) {
    Color bg;
    Color fg;
    String label = status.replaceAll('_', ' ').toUpperCase();

    switch (status.toLowerCase()) {
      case 'active':
      case 'approved':
      case 'paid':
      case 'completed':
      case 'available':
      case 'published':
        bg = AppTheme.success.withOpacity(0.12);
        fg = AppTheme.success;
        break;
      case 'pending':
      case 'pending_signatures':
      case 'under_review':
      case 'reserved':
      case 'draft':
      case 'assigned':
      case 'in_progress':
        bg = AppTheme.warning.withOpacity(0.12);
        fg = AppTheme.warning;
        break;
      case 'rejected':
      case 'cancelled':
      case 'overdue':
      case 'terminated':
      case 'damaged':
      case 'emergency':
        bg = AppTheme.error.withOpacity(0.12);
        fg = AppTheme.error;
        break;
      case 'occupied':
      case 'high':
        bg = AppTheme.secondary.withOpacity(0.12);
        fg = AppTheme.secondary;
        break;
      default:
        bg = Colors.grey.withOpacity(0.12);
        fg = Colors.grey;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: fg.withOpacity(0.3), width: 1),
      ),
      child: Text(
        label,
        style: GoogleFonts.inter(
          color: fg,
          fontSize: fontSize,
          fontWeight: FontWeight.w700,
          letterSpacing: 0.4,
        ),
      ),
    );
  }
}
