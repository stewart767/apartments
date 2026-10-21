import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../app/theme.dart';

class PriceFormatter {
  static String format(double amount, {String currency = 'TZS'}) {
    final formatter = NumberFormat('#,###');
    return '$currency ${formatter.format(amount)}';
  }
}

class PriceCard extends StatelessWidget {
  final double price;
  final String billingPeriod;
  final String currency;
  final double fontSize;

  const PriceCard({
    super.key,
    required this.price,
    this.billingPeriod = 'monthly',
    this.currency = 'TZS',
    this.fontSize = 18,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      crossAxisAlignment: CrossAxisAlignment.baseline,
      textBaseline: TextBaseline.alphabetic,
      children: [
        Text(
          PriceFormatter.format(price, currency: currency),
          style: GoogleFonts.outfit(
            fontSize: fontSize,
            fontWeight: FontWeight.bold,
            color: AppTheme.primary,
          ),
        ),
        const SizedBox(width: 4),
        Text(
          '/$billingPeriod',
          style: GoogleFonts.inter(
            fontSize: fontSize * 0.65,
            color: Theme.of(context).brightness == Brightness.dark
                ? AppTheme.textMutedDark
                : AppTheme.textMutedLight,
          ),
        ),
      ],
    );
  }
}
